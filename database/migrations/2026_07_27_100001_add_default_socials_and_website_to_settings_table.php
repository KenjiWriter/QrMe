<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->string('default_website_url')->nullable()->after('qr_color');
            $table->string('default_facebook_url')->nullable()->after('default_website_url');
            $table->string('default_instagram_url')->nullable()->after('default_facebook_url');
            $table->string('default_linkedin_url')->nullable()->after('default_instagram_url');
            $table->string('default_youtube_url')->nullable()->after('default_linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn([
                'default_website_url',
                'default_facebook_url',
                'default_instagram_url',
                'default_linkedin_url',
                'default_youtube_url',
            ]);
        });
    }
};
