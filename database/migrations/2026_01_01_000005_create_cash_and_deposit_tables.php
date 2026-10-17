<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Serah Terima Kas Kasir -> Kolektor (Cash Handovers)
        Schema::create('cash_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('handover_number', 30)->unique()->index();
            $table->foreignId('cashier_user_id')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('collector_user_id')->constrained('users')->cascadeOnUpdate();
            $table->timestamp('handover_date')->useCurrent()->index();
            $table->decimal('system_amount', 15, 2);
            $table->decimal('physical_amount', 15, 2);
            $table->decimal('difference_amount', 15, 2)->default(0); // physical - system
            $table->text('difference_reason')->nullable();
            $table->enum('status', ['submitted', 'received'])->default('submitted')->index();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Setoran Kas Kolektor -> Bank / Bapenda (Bank Deposits)
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_number', 30)->unique()->index();
            $table->foreignId('collector_user_id')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('verifier_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('deposit_date')->index();
            $table->string('destination_bank', 100);
            $table->decimal('deposit_amount', 15, 2);
            $table->string('bank_ref_number', 100)->nullable();
            $table->string('proof_file_path')->nullable();
            $table->enum('status', ['submitted', 'approved', 'rejected'])->default('submitted')->index();
            $table->text('rejection_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
        Schema::dropIfExists('cash_handovers');
    }
};
