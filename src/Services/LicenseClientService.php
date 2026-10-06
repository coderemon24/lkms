<?php

namespace Lkms\Client\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LicenseClientService
{
    public function __construct(
        protected FingerprintService $fingerprint,
        protected LeaseStorageService $storage
    ) {
    }

    /**
     * Send activation request to central LKMS server.
     */
    public function activate(string $licenseKey): array
    {
        $baseUrl = config('lkms.server_url');
        if (empty($baseUrl)) {
            return [
                'success' => false,
                'message' => 'Central license server URL is missing or not configured in config/lkms.php.',
            ];
        }

        $serverUrl = rtrim((string) $baseUrl, '/') . '/license/activate';
        $meta = $this->fingerprint->collect();

        $nonce = Str::random(32);
        $timestamp = (string) time();

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-Nonce' => $nonce,
                    'X-Timestamp' => $timestamp,
                    'Accept' => 'application/json',
                ])
                ->post($serverUrl, [
                    'license_key' => trim($licenseKey),
                    'domain' => $meta['domain'],
                    'installation_hash' => $meta['installation_hash'],
                    'server_ip' => $meta['server_ip'],
                    'server_hostname' => $meta['server_hostname'],
                    'environment_meta' => [
                        'php_version' => $meta['php_version'],
                        'laravel_version' => $meta['laravel_version'],
                    ],
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false)) {
                $leaseToken = $data['data']['lease_token'] ?? null;
                if ($leaseToken && $this->storage->saveLease($licenseKey, $leaseToken)) {
                    return [
                        'success' => true,
                        'message' => $data['message'] ?? 'Software activated successfully.',
                        'data' => $data['data'] ?? [],
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'License server responded, but lease token verification failed.',
                ];
            }

            $lockMsg = $data['lock_message'] ?? $data['message'] ?? 'License activation was rejected by central authority.';
            $contact = $data['support_contact'] ?? null;

            if (in_array($data['status'] ?? '', ['suspended', 'revoked', 'expired'])) {
                $this->storage->revokeLocalLease($data['status'], $lockMsg, $contact);
            }

            return [
                'success' => false,
                'message' => $lockMsg,
                'lock_message' => $lockMsg,
                'support_contact' => $contact,
                'errors' => $data['errors'] ?? [],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Could not connect to central License Server: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send periodic heartbeat to central LKMS server.
     */
    public function heartbeat(): array
    {
        $details = $this->storage->getLeaseDetails();
        if (!$details) {
            return ['success' => false, 'message' => 'No active license to sync.'];
        }

        $baseUrl = config('lkms.server_url');
        if (empty($baseUrl)) {
            return [
                'success' => false,
                'message' => 'Central license server URL is missing or not configured in config/lkms.php.',
            ];
        }

        $serverUrl = rtrim((string) $baseUrl, '/') . '/license/heartbeat';
        $meta = $this->fingerprint->collect();

        try {
            $response = Http::timeout(10)->post($serverUrl, [
                'license_key' => $details['license_key'],
                'installation_hash' => $meta['installation_hash'],
                'domain' => $meta['domain'],
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false)) {
                $newLeaseToken = $data['data']['lease_token'] ?? null;
                if ($newLeaseToken) {
                    $this->storage->saveLease($details['license_key'], $newLeaseToken);
                }
                return [
                    'success' => true,
                    'message' => 'Heartbeat acknowledged and lease renewed.',
                ];
            }

            $lockMsg = $data['lock_message'] ?? $data['message'] ?? 'License has been locked by authority.';
            $contact = $data['support_contact'] ?? null;

            // Check if server revoked, suspended, or expired this license
            if (in_array($data['status'] ?? '', ['revoked', 'suspended', 'expired', 'domain_mismatch'])) {
                $this->storage->revokeLocalLease($data['status'], $lockMsg, $contact);
            }

            return [
                'success' => false,
                'message' => $lockMsg,
                'lock_message' => $lockMsg,
                'support_contact' => $contact,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Server unreachable: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check / sync license status on demand.
     */
    public function sync(): array
    {
        return $this->heartbeat();
    }
}
