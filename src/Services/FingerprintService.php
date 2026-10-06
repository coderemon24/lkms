<?php

namespace Lkms\Client\Services;

class FingerprintService
{
    /**
     * Normalize the current domain name.
     */
    public function getDomain(): string
    {
        $host = request()->getHost();

        if (empty($host) || $host === 'localhost' || $host === '127.0.0.1') {
            $parsed = parse_url(config('app.url'), PHP_URL_HOST);
            if (!empty($parsed)) {
                $host = $parsed;
            }
        }

        $host = strtolower(trim($host));
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host ?: 'localhost';
    }

    /**
     * Compute a deterministic machine/installation hash.
     */
    public function getInstallationHash(): string
    {
        $entropy = implode('|', [
            base_path(),
            php_uname('n'),
            config('app.key', 'lkms_default_seed'),
        ]);

        return hash('sha256', $entropy);
    }

    /**
     * Collect machine and environment telemetry.
     */
    public function collect(): array
    {
        return [
            'domain' => $this->getDomain(),
            'installation_hash' => $this->getInstallationHash(),
            'server_ip' => request()->server('SERVER_ADDR') ?: gethostbyname(gethostname()),
            'server_hostname' => gethostname() ?: 'unknown',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }
}
