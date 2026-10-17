<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->string('regency_logo_path')->nullable()->after('logo_path');
            $table->string('hero_image_path')->nullable()->after('regency_logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn(['regency_logo_path', 'hero_image_path']);
        });
    }
};
