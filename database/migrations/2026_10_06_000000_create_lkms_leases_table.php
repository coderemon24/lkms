<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lkms_leases')) {
            Schema::create('lkms_leases', function (Blueprint $table) {
                $table->id();
                $table->string('license_key', 64);
                $table->longText('lease_token');
                $table->string('domain', 255);
                $table->string('installation_hash', 64);
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->string('status', 32)->default('active');
                $table->text('lock_message')->nullable();
                $table->string('support_contact', 255)->nullable();
                $table->string('hmac_checksum', 64);
                $table->timestamps();
            });
        } else {
            Schema::table('lkms_leases', function (Blueprint $table) {
                if (!Schema::hasColumn('lkms_leases', 'lock_message')) {
                    $table->text('lock_message')->nullable();
                }
                if (!Schema::hasColumn('lkms_leases', 'support_contact')) {
                    $table->string('support_contact', 255)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lkms_leases');
    }
};
