<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Wajib Pajak (Taxpayers)
        Schema::create('taxpayers', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->nullable()->index();
            $table->string('name')->index();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('dusun', 50)->nullable()->index();
            $table->string('village_name', 100)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('regency_name', 100)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Objek Pajak / NOP (Tax Objects)
        Schema::create('tax_objects', function (Blueprint $table) {
            $table->id();
            $table->string('nop', 25)->unique()->index();
            $table->foreignId('taxpayer_id')->constrained('taxpayers')->cascadeOnUpdate();
            $table->text('address')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('dusun', 50)->nullable()->index();
            $table->enum('status', ['normal', 'to'])->default('normal')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Komponen Objek Pajak (Bumi, Bangunan)
        Schema::create('tax_object_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_object_id')->constrained('tax_objects')->cascadeOnDelete();
            $table->enum('type', ['bumi', 'bangunan', 'bumi_bersama', 'bangunan_bersama']);
            $table->decimal('area_m2', 10, 2)->default(0);
            $table->string('class_code', 10)->nullable();
            $table->decimal('njop_per_m2', 15, 2)->default(0);
            $table->decimal('total_njop', 15, 2)->default(0);
            $table->smallInteger('tax_year');
            $table->timestamps();
        });

        // 4. Ketetapan SPPT per Tahun Pajak
        Schema::create('sppts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_object_id')->constrained('tax_objects')->cascadeOnUpdate();
            $table->smallInteger('tax_year')->index();
            $table->date('due_date')->nullable();
            $table->decimal('njop_dasar', 15, 2)->default(0);
            $table->decimal('njoptkp', 15, 2)->default(0);
            $table->decimal('njop_perhitungan', 15, 2)->default(0);
            $table->decimal('tarif_percent', 6, 4)->default(0.0030);
            $table->decimal('pbb_terutang', 15, 2)->default(0); // Official assessed tax from Bapenda
            $table->decimal('fine_amount', 15, 2)->default(0);
            $table->decimal('total_bill', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid')->index();
            $table->boolean('is_to_blocked')->default(false)->index();
            $table->string('dhkp_row_ref')->nullable();
            $table->timestamps();

            $table->unique(['tax_object_id', 'tax_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sppts');
        Schema::dropIfExists('tax_object_components');
        Schema::dropIfExists('tax_objects');
        Schema::dropIfExists('taxpayers');
    }
};
