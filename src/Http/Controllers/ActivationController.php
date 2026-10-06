<?php

namespace Lkms\Client\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Lkms\Client\Services\FingerprintService;
use Lkms\Client\Services\LeaseStorageService;
use Lkms\Client\Services\LicenseClientService;

class ActivationController extends Controller
{
    public function __construct(
        protected LeaseStorageService $storage,
        protected LicenseClientService $clientService,
        protected FingerprintService $fingerprint
    ) {
    }

    /**
     * Display the license activation form.
     * Prevents access if software is already active!
     */
    public function showActivate(Request $request): View|RedirectResponse
    {
        if (!$this->storage->isConfigValid()) {
            return redirect()->route('lkms.locked');
        }

        // Once active, lock out this route unless ?force=1 is passed
        if ($this->storage->isSoftwareActive() && !$request->boolean('force')) {
            return redirect(config('lkms.redirect_after_activation', '/'))
                ->with('info', 'Your software license is already active and cryptographically verified.');
        }

        $domain = $this->fingerprint->getDomain();
        $details = $this->storage->getLeaseDetails();

        return view('lkms::activate', compact('domain', 'details'));
    }

    /**
     * Submit license key to central server.
     */
    public function submitActivate(Request $request): RedirectResponse
    {
        $request->validate([
            'license_key' => ['required', 'string', 'max:64'],
        ]);

        $key = strtoupper(trim($request->input('license_key')));
        $result = $this->clientService->activate($key);

        if ($result['success']) {
            return redirect(config('lkms.redirect_after_activation', '/'))
                ->with('success', 'License activated and lease verified successfully! Software is ready.');
        }

        return back()
            ->withInput()
            ->withErrors(['license_key' => $result['message']]);
    }

    /**
     * Display locked screen when software is unlicensed or expired.
     */
    public function showLocked(Request $request): View|RedirectResponse
    {
        // Allow on-demand sync with central authority
        if ($request->boolean('sync')) {
            $syncResult = $this->clientService->heartbeat();
            if ($syncResult['success'] && $this->storage->isSoftwareActive()) {
                return redirect(config('lkms.redirect_after_activation', '/'))
                    ->with('success', 'License verified with central authority! Software unlocked.');
            }
        }

        if (!$this->storage->isConfigValid()) {
            $domain = $this->fingerprint->getDomain();
            $details = [
                'status' => 'locked',
                'lock_message' => 'Software configuration integrity check failed: config/lkms.php is missing or critical license settings (server_url, public_key) have been removed or tampered.',
                'support_contact' => config('lkms.support_contact', 'support@yourdomain.com'),
            ];
            return view('lkms::locked', compact('domain', 'details'));
        }

        if ($this->storage->isSoftwareActive()) {
            return redirect(config('lkms.redirect_after_activation', '/'));
        }

        $domain = $this->fingerprint->getDomain();
        $details = $this->storage->getLeaseDetails();

        if (!$details) {
            return redirect()->route('lkms.activate');
        }

        return view('lkms::locked', compact('domain', 'details'));
    }
}
