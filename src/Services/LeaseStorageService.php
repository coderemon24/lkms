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
     * Determine if software is currently licensed and active.
     */
    public function isSoftwareActive(): bool
    {
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

        // 4. Offline Grace Period Check
        $graceDays = config('lkms.grace_period_days', 7);
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
            $syncMinutes = (int) $syncMinutes;
            if ($syncMinutes <= 0) {
                return true;
            }
            return Carbon::parse($record->last_synced_at)->addMinutes($syncMinutes)->isPast();
        }

        $syncHours = (int) (config('lkms.heartbeat_hours') ?? 0);
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
