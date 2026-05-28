<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('qr_color', 7)->nullable()->after('qr_code_path');
            $table->string('qr_eye_shape', 10)->nullable()->after('qr_color');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn(['qr_color', 'qr_eye_shape']);
        });
    }
};
