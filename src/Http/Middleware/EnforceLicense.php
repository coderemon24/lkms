<?php

namespace Lkms\Client\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lkms\Client\Services\LeaseStorageService;
use Symfony\Component\HttpFoundation\Response;

class EnforceLicense
{
    public function __construct(protected LeaseStorageService $storage)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow activation and locked screens without restriction
        if ($request->is('license/*') || $request->is('license')) {
            return $next($request);
        }

        if (!$this->storage->isSoftwareActive()) {
            $details = $this->storage->getLeaseDetails();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => $details['status'] ?? 'unlicensed',
                    'message' => $details['lock_message'] ?? 'Software license is invalid, expired, or locked on this domain.',
                    'support_contact' => $details['support_contact'] ?? null,
                ], 403);
            }

            if ($details && in_array($details['status'] ?? '', ['revoked', 'suspended', 'expired', 'domain_mismatch', 'locked'])) {
                return redirect()->route('lkms.locked');
            }

            return redirect()->route('lkms.activate');
        }

        return $next($request);
    }
}
