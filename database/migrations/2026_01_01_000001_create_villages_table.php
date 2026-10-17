<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Desa Sukahaji');
            $table->string('district')->default('Kecamatan Patrol');
            $table->string('regency')->default('Kabupaten Indramayu');
            $table->string('province')->default('Jawa Barat');
            $table->string('postal_code')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('kades_name')->nullable();
            $table->string('kades_nip')->nullable();
            $table->smallInteger('active_tax_year')->default(2026);
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->text('bank_account_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
