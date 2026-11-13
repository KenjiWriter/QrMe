<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_qrs', function (Blueprint $table): void {
            // 'url'  — QR points to /qr/link/{short_id} (redirect + scan counter)
            // 'wifi' — QR encodes the network credentials directly (WIFI:...;;)
            $table->string('type', 10)->default('url')->after('name');
            $table->string('url')->nullable()->change();

            $table->string('wifi_ssid')->nullable()->after('url');
            // Stored with Laravel's "encrypted" cast, hence text.
            $table->text('wifi_password')->nullable()->after('wifi_ssid');
            $table->string('wifi_encryption', 10)->nullable()->after('wifi_password');
            $table->boolean('wifi_hidden')->default(false)->after('wifi_encryption');
            $table->string('wifi_eap_method', 10)->nullable()->after('wifi_hidden');
            $table->string('wifi_eap_identity')->nullable()->after('wifi_eap_method');
        });
    }

    public function down(): void
    {
        DB::table('custom_qrs')->whereNull('url')->update(['url' => '']);

        Schema::table('custom_qrs', function (Blueprint $table): void {
            $table->dropColumn([
                'type',
                'wifi_ssid',
                'wifi_password',
                'wifi_encryption',
                'wifi_hidden',
                'wifi_eap_method',
                'wifi_eap_identity',
            ]);
            $table->string('url')->nullable(false)->change();
        });
    }
};
