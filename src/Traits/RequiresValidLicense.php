<?php

namespace Lkms\Client\Traits;

use Lkms\Client\Services\LeaseStorageService;

/**
 * Deep DRM Defense Trait:
 * Embed this trait directly into your client application's core Eloquent Models,
 * Base Controllers, or Critical Service classes.
 *
 * Even if a user attempts to remove or bypass the HTTP middleware, this trait
 * enforces license validity at the database query and controller lifecycle layer.
 *
 * Furthermore, removing this package from composer causes a fatal class-not-found error,
 * preventing package removal attacks.
 */
trait RequiresValidLicense
{
    /**
     * Automatically hook into Eloquent model lifecycle.
     */
    public static function bootRequiresValidLicense(): void
    {
        static::retrieving(function () {
            self::checkLicenseOrAbort();
        });

        static::saving(function () {
            self::checkLicenseOrAbort();
        });
    }

    /**
     * Manual guard method for base controllers or services.
     */
    public function ensureLicenseActive(): void
    {
        self::checkLicenseOrAbort();
    }

    /**
     * Core cryptographic validation guard.
     */
    protected static function checkLicenseOrAbort(): void
    {
        if (!app()->bound(LeaseStorageService::class)) {
            abort(403, 'License engine integrity check failed.');
        }

        $storage = app(LeaseStorageService::class);

        if (!$storage->isSoftwareActive()) {
            if (function_exists('request') && request()->is('license/*') || request()->is('license')) {
                return; // Exclude activation/lock screens
            }

            if (function_exists('request') && request()->expectsJson()) {
                abort(response()->json([
                    'success' => false,
                    'status' => 'license_locked',
                    'message' => 'Application execution halted: Valid commercial license required.',
                ], 403));
            }

            if (function_exists('route')) {
                redirect()->route('lkms.locked')->send();
                exit;
            }

            abort(403, 'Application execution halted: Valid commercial license required.');
        }
    }
}
