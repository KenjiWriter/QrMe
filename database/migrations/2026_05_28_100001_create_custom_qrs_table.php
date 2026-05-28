<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_qrs', function (Blueprint $table): void {
            $table->id();
            $table->string('short_id', 16)->unique();
            $table->string('name');
            $table->string('url');
            $table->string('qr_color', 7)->default('#000000');
            $table->string('qr_eye_shape', 10)->default('square');
            $table->string('logo_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->unsignedBigInteger('scan_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_qrs');
    }
};
