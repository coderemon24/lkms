# LKMS Software Licensing Client (`coderemon24/lkms`)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/coderemon24/lkms.svg?style=flat-square)](https://packagist.org/packages/coderemon24/lkms)
[![Total Downloads](https://img.shields.io/packagist/dt/coderemon24/lkms.svg?style=flat-square)](https://packagist.org/packages/coderemon24/lkms)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

A zero-configuration, drop-in commercial software licensing client for Laravel applications. Features asymmetric RSA-2048 digital signature verification, dynamic domain and hardware binding, an offline grace period, active-state route lockout, centralized remote lock messaging, stealth auto-enforcement, cron-less automatic heartbeat synchronization, and bytecode anti-tamper protection.

---

## ⚡ 1. Quick Installation

In your client Laravel application (e.g., `kazilawchamber.com`), install via Composer:

```bash
composer require coderemon24/lkms
```

Then run the automated setup command:

```bash
php artisan lkms:install
```

> **What `lkms:install` does:**
> 1. Publishes `config/lkms.php`
> 2. Automatically executes the database migration for `lkms_leases`

---

## 🛡️ 2. Protection Modes & User Manual

### Mode A: Stealth Auto-Enforce (Recommended — 100% Invisible)
By default, the package runs in stealth mode. **You do NOT need to touch `bootstrap/app.php` or `routes/web.php`!**

The package automatically injects the license enforcement middleware into Laravel's global `web` pipeline behind the scenes:
```php
// config/lkms.php
'auto_enforce' => env('LKMS_AUTO_ENFORCE', true),
```
* Every page and route is automatically protected.
* Unlicensed or locked installations are immediately caught and redirected to `/license/activate` or `/license/locked`.
* No visible middleware is registered in the client's code, so it cannot be easily spotted or removed.

---

### Mode B: Deep Defense Trait (`RequiresValidLicense`)
To prevent anyone from bypassing the middleware or deleting the package from `composer.json`, add this trait to your application's 1-2 core Eloquent Models (e.g. `User.php`, `Client.php`, `Invoice.php`):

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lkms\Client\Traits\RequiresValidLicense;

class Client extends Model
{
    use RequiresValidLicense; // 🔒 Deep DRM Lock

    // ...
}
```

#### Why use this Trait?
1. **Database-Level Guard:** Even if an attacker manages to bypass the HTTP middleware, the moment any controller queries the database (`Client::all()`, `find()`, or `save()`), this trait halts execution and redirects to `/license/locked`.
2. **The "Poison Pill" (Package Removal Trap):** If someone deletes the `coderemon24/lkms` package from `composer.json`, PHP will immediately throw a fatal error: `Trait 'Lkms\Client\Traits\RequiresValidLicense' not found`. The entire application instantly crashes and cannot be run!

---

### Mode C: Manual Route Group Protection (Optional)
If you disabled `auto_enforce` and prefer manual control over specific routes:

In `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'license.enforce' => \Lkms\Client\Http\Middleware\EnforceLicense::class,
    ]);
})
```
Then protect routes in `routes/web.php`:
```php
Route::middleware(['license.enforce'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // All protected business routes...
});
```

---

## ⚙️ 3. Zero-.env Configuration

All sensitive cryptographic parameters are pre-embedded directly inside [`config/lkms.php`](config/lkms.php):

* **Central LKMS Endpoint:** Points to your central licensing authority.
* **Embedded RSA-2048 Public Verification Key:** Embedded directly as a PEM constant (customers cannot alter or replace it).
* **API Client Credentials:** Pre-set inside the package.

```php
// config/lkms.php
return [
    'server_url' => env('LKMS_SERVER_URL', 'https://license.yourdomain.com/api/v1'),
    'public_key' => <<<PEM
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtXhmCfX9ktjGoIkjyFsi
PQ7jFTT0S9M/hrUN/aK85xysAwxXZC+Wyib3gBwy/cWCxtlLPqSyLCG/oAyRILLe...
-----END PUBLIC KEY-----
PEM,
    'grace_period_days' => 7,
    'heartbeat_hours' => 24,
    'auto_enforce' => env('LKMS_AUTO_ENFORCE', true),
    'redirect_after_activation' => '/',
];
```

---

## 🌐 4. The Activation Workflow (`/license/activate`)

1. **First-Time Visit:**  
   When an unlicensed user visits the application, they are automatically redirected to:
   ```
   https://kazilawchamber.com/license/activate
   ```
2. **License Key Input:**  
   The client enters the license key issued from your LKMS Admin Console (e.g., `LKMS-LIVE-KAZI-2026-ABCD`).
3. **Cryptographic Handshake:**  
   The package sends an authenticated request to your central server (`POST /api/v1/license/activate`) with:
   * Host Domain (e.g., `kazilawchamber.com`)
   * Machine installation hash
   * Server IP & environment metadata
4. **Digital Signature Verification:**  
   Your central authority verifies the domain and quota, digitally signs an RSA-2048 Lease Token, and returns it. The client package cryptographically validates the signature and stores it in the local database.
5. **Instant Unlock:**  
   The software is immediately unlocked and redirects to `/`.

---

## 🛑 5. Active State Lockout Protection

* Once the software is active, **access to `/license/activate` is blocked automatically**. Any attempts to visit it will redirect straight to `/`.
* If the license key needs to be updated or re-entered, pass `?force=1`:
  ```
  https://kazilawchamber.com/license/activate?force=1
  ```

---

## ⏰ 6. Heartbeat Sync Engine & Offline Grace Period

### Is a Cron Job Mandatory? **NO!**
LKMS features a **Dual-Sync Engine**: it syncs automatically through regular visitor traffic even if the client never sets up a Cron Job!

#### Mode 1: Automated Web-Traffic Sync (Zero Setup / Cron-less)
* When normal users or visitors browse any page of the client application, the `EnforceLicense` middleware checks whether 24 hours have elapsed since the last heartbeat (`shouldSyncHeartbeat()`).
* If sync is due, the package triggers the heartbeat using Laravel's `app()->terminating(...)` hook.
* **Zero Latency:** The terminating callback executes **after** the HTTP response has already been sent to the visitor's browser (FastCGI finish request). The visitor experiences **zero lag or page delay**.
* If a visitor visits the site even once a day, the lease stays renewed seamlessly!

#### Mode 2: Scheduled Cron Job (Optional / Recommended for Low-Traffic Sites)
If the client site receives very little traffic, add the scheduled command to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('lkms:heartbeat')->daily();
```

* **Lease Renewal:** Renews the 24-hour cryptographic lease token with the central authority.
* **Offline Grace Period (7 Days):** If the client server loses internet or your central licensing server experiences maintenance, the client software continues to function normally for **7 full days** (`grace_period_days => 7`).
* **Remote Kill-Switch:** If you suspend, revoke, or lock a license in your LKMS Admin Dashboard, the client software locks automatically upon the next heartbeat sync.

---

## 📢 7. Centralized Custom Lock Screen Message

When a client application is locked or suspended, you can show a **customized notice directly from your LKMS main server**:

1. **Set Custom Notice on Main Server:**  
   In your LKMS Admin Dashboard (`Edit License` or `Toggle Status`), write any custom message (e.g., *"Subscription suspended due to overdue invoice #INV-9921. Please contact accounts@kazilawchamber.com"*).
2. **Instant Delivery:**  
   During heartbeat synchronization or failed activation, the main server delivers your custom lock message and support contact.
3. **Locked Screen Display (`/license/locked`):**  
   The client application renders a high-visibility alert banner featuring your exact custom message and support hotline/email.
4. **On-Demand Re-Sync Button:**  
   The locked screen includes a **"Re-check / Sync Status with Server"** action. Once you restore the license to `active` on your main server, the client clicks this button and is immediately unlocked without needing to re-enter anything!

---

## ❓ 8. Security & Tamper FAQ: What Happens If Someone Removes `server_url`?

If a client attempts to bypass the licensing system by deleting or emptying `'server_url'` in `config/lkms.php`, here is what happens:

### Case 1: Unactivated Installation
* The client visits `/license/activate` and tries to submit a license key.
* The activation request fails immediately with the error:
  > `"Central license server URL is missing or not configured in config/lkms.php."`
* No RSA-2048 lease token is generated. **The application remains 100% locked.**

### Case 2: Already Activated Installation
* For standard web requests, the package verifies the lease locally using the embedded RSA Public Key and DB HMAC checksum (it does not require an active server connection on every HTTP request).
* **HOWEVER:** Because `server_url` is removed, the background heartbeat (via cron or web-traffic) fails to reach the central server to renew the lease timestamp (`last_synced_at`).
* Once the **7-Day Grace Period** (`grace_period_days => 7`) expires:
  ```php
  // LeaseStorageService.php
  if ($lastSynced && $lastSynced->addDays($graceDays)->isPast()) {
      return false; // Grace period expired without online heartbeat!
  }
  ```
* **Result:** After 7 days, the software automatically and permanently **self-locks**, redirecting all visitors to `/license/locked`. The client cannot bypass the license by removing the server URL!

---

## 🔐 9. Bytecode Encoding (ionCube / SourceGuardian)

To ensure clients cannot open and tamper with the PHP source files on their server:
1. Run the encoder compiler:
   * **Windows:** Run `packages/lkms/client-license/build/encode-ioncube.bat`
   * **Linux:** Run `bash packages/lkms/client-license/build/encode-ioncube.sh`
2. Distribute the compiled bytecode in `dist/encoded-src/` in place of `src/`.

---

## 📄 License
The MIT License (MIT). Created by [Ahmed Emon](https://github.com/coderemon24).
