<?php

namespace Lkms\Client\Services;

class SignatureVerifierService
{
    /**
     * Verify an asymmetrically signed token using the embedded public key.
     */
    public function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $encodedSignature] = $parts;

        $payloadJson = $this->base64UrlDecode($encodedPayload);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (!$payloadJson || !$signature) {
            return null;
        }

        $publicKey = config('lkms.public_key');
        if (empty($publicKey)) {
            return null;
        }

        $verified = openssl_verify(
            $encodedPayload,
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            return null; // Cryptographic signature invalid or tampered!
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return null;
        }

        // Validate expiration if present
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // Token expired
        }

        return $payload;
    }

    private function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
