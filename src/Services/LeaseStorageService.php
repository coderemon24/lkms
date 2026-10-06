<?php

namespace Lkms\Client\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeaseStorageService
{
    public function __construct(
        protected SignatureVerifierService $verifier,
        protected FingerprintService $fingerprint
    ) {
    }

    /**
     * Save newly acquired or renewed lease token.
     */
    public function saveLease(string $licenseKey, string $leaseToken): bool
    {
        $payload = $this->verifier->verifyToken($leaseToken);
        if (!$payload) {
            return false;
        }

        $domain = $this->fingerprint->getDomain();
        $installationHash = $this->fingerprint->getInstallationHash();
        $expiresAt = isset($payload['expires_at']) ? Carbon::parse($payload['expires_at']) : null;

        $checksum = $this->calculateChecksum($licenseKey, $leaseToken, $installationHash);

        DB::table('lkms_leases')->truncate();

        DB::table('lkms_leases')->insert([
            'license_key' => $licenseKey,
            'lease_token' => $leaseToken,
            'domain' => $domain,
            'installation_hash' => $installationHash,
            'expires_at' => $expiresAt,
            'last_synced_at' => now(),
            'status' => 'active',
            'lock_message' => null,
            'support_contact' => null,
            'hmac_checksum' => $checksum,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Validate the presence and integrity of config/lkms.php and mandatory parameters.
     */
    public function isConfigValid(): bool
    {
        // 1. Config file existence check in Laravel application
        if (function_exists('config_path')) {
            $publishedConfig = config_path('lkms.php');
            if (!file_exists($publishedConfig)) {
                return false;
            }
        }

        // 2. Central Server URL check
        $serverUrl = config('lkms.server_url');
        if (empty($serverUrl) || !is_string($serverUrl) || (!str_starts_with($serverUrl, 'http://') && !str_starts_with($serverUrl, 'https://'))) {
            return false;
        }

        // 3. Embedded Cryptographic Public Key check
        $publicKey = config('lkms.public_key');
        if (empty($publicKey) || !is_string($publicKey)) {
            return false;
        }
        if (!str_contains($publicKey, '-----BEGIN PUBLIC KEY-----') || !str_contains($publicKey, '-----END PUBLIC KEY-----')) {
            return false;
        }

        // 4. Offline Grace Period check (Allowed envelope: 1 to 14 days maximum)
        $graceDays = config('lkms.grace_period_days');
        if ($graceDays === null || !is_numeric($graceDays) || (int) $graceDays < 1 || (int) $graceDays > 14) {
            return false; // Tampered grace period!
        }

        // 5. Heartbeat Interval check (Maximum allowed interval is 24 hours / 1440 minutes)
        $syncMinutes = config('lkms.heartbeat_minutes');
        if ($syncMinutes !== null && (!is_numeric($syncMinutes) || (int) $syncMinutes < 0 || (int) $syncMinutes > 1440)) {
            return false; // Tampered heartbeat interval!
        }

        $syncHours = config('lkms.heartbeat_hours');
        if ($syncHours !== null && (!is_numeric($syncHours) || (int) $syncHours < 0 || (int) $syncHours > 24)) {
            return false; // Tampered heartbeat hours!
        }

        // 6. Stealth Auto-Enforce check
        if (config('lkms.auto_enforce') !== true) {
            return false;
        }

        // 7. API Client ID check
        $clientId = config('lkms.client_id');
        if (empty($clientId) || !is_string($clientId) || strlen($clientId) < 8) {
            return false;
        }

        // 8. API Client Secret check
        $clientSecret = config('lkms.client_secret');
        if (empty($clientSecret) || !is_string($clientSecret) || strlen($clientSecret) < 8) {
            return false;
        }

        // 9. Routes configuration check
        $routes = config('lkms.routes');
        if (!is_array($routes) || empty($routes['prefix']) || !is_string($routes['prefix'])) {
            return false;
        }

        return true;
    }

    /**
     * Determine if software is currently licensed and active.
     */
    public function isSoftwareActive(): bool
    {
        if (!$this->isConfigValid()) {
            return false;
        }

        if (!Schema::hasTable('lkms_leases')) {
            return false;
        }

        $record = DB::table('lkms_leases')->first();
        if (!$record || $record->status !== 'active') {
            return false;
        }

        // 1. Check local DB HMAC integrity (detect manual SQL row edits)
        $expectedHmac = $this->calculateChecksum($record->license_key, $record->lease_token, $record->installation_hash);
        if (!hash_equals($expectedHmac, $record->hmac_checksum)) {
            return false; // Database tampered!
        }

        // 2. Cryptographic RSA Token Verification
        $payload = $this->verifier->verifyToken($record->lease_token);
        if (!$payload) {
            return false; // Signature invalid or forged!
        }

        // 3. Machine & Domain Binding Check
        $currentDomain = $this->fingerprint->getDomain();
        $currentHash = $this->fingerprint->getInstallationHash();

        if ($record->installation_hash !== $currentHash) {
            return false; // Copied across machines!
        }

        // 4. Offline Grace Period Check (Capped at 14 days maximum ceiling)
        $graceDays = min((int) config('lkms.grace_period_days', 7), 14);
        $lastSynced = $record->last_synced_at ? Carbon::parse($record->last_synced_at) : null;
        if ($lastSynced && $lastSynced->addDays($graceDays)->isPast()) {
            return false; // Grace period expired without online heartbeat!
        }

        // 5. Expiration Check
        if ($record->expires_at) {
            $expiry = Carbon::parse($record->expires_at);
            if ($expiry->isPast()) {
                return false; // License term expired
            }
        }

        return true;
    }

    /**
     * Get current active lease details.
     */
    public function getLeaseDetails(): ?array
    {
        if (!Schema::hasTable('lkms_leases')) {
            return null;
        }

        $record = DB::table('lkms_leases')->first();
        if (!$record) {
            return null;
        }

        return [
            'license_key' => $record->license_key,
            'domain' => $record->domain,
            'status' => $record->status,
            'expires_at' => $record->expires_at,
            'last_synced_at' => $record->last_synced_at,
            'lock_message' => $record->lock_message ?? null,
            'support_contact' => $record->support_contact ?? null,
        ];
    }

    /**
     * Determine if a periodic heartbeat synchronization is due.
     */
    public function shouldSyncHeartbeat(): bool
    {
        if (!Schema::hasTable('lkms_leases')) {
            return false;
        }

        $record = DB::table('lkms_leases')->first();
        if (!$record || $record->status !== 'active') {
            return false;
        }

        if (empty($record->last_synced_at)) {
            return true;
        }

        $syncMinutes = config('lkms.heartbeat_minutes');
        if ($syncMinutes !== null) {
            $syncMinutes = min((int) $syncMinutes, 1440);
            if ($syncMinutes <= 0) {
                return true;
            }
            return Carbon::parse($record->last_synced_at)->addMinutes($syncMinutes)->isPast();
        }

        $syncHours = min((int) (config('lkms.heartbeat_hours') ?? 0), 24);
        if ($syncHours <= 0) {
            return true;
        }

        return Carbon::parse($record->last_synced_at)->addHours($syncHours)->isPast();
    }

    /**
     * Clear and delete local lease completely (e.g. when license is deleted from authority).
     */
    public function clearLease(): void
    {
        if (Schema::hasTable('lkms_leases')) {
            DB::table('lkms_leases')->truncate();
        }
    }

    /**
     * Revoke or suspend local lease immediately with server lock message.
     */
    public function revokeLocalLease(string $status = 'revoked', ?string $lockMessage = null, ?string $supportContact = null): void
    {
        if (Schema::hasTable('lkms_leases')) {
            $data = ['status' => $status];
            if ($lockMessage !== null) {
                $data['lock_message'] = $lockMessage;
            }
            if ($supportContact !== null) {
                $data['support_contact'] = $supportContact;
            }
            DB::table('lkms_leases')->update($data);
        }
    }

    private function calculateChecksum(string $key, string $token, string $hash): string
    {
        $appKey = config('app.key', 'lkms_salt');
        return hash_hmac('sha256', $key . '|' . $token . '|' . $hash, $appKey);
    }
}
