<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Transaksi Pembayaran (Payments)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 30)->unique()->index();
            $table->string('verification_token', 64)->unique()->index();
            $table->foreignId('sppt_id')->constrained('sppts')->cascadeOnUpdate();
            $table->foreignId('cashier_user_id')->constrained('users')->cascadeOnUpdate();
            $table->timestamp('payment_date')->useCurrent()->index();
            $table->decimal('amount_principal', 15, 2);
            $table->decimal('amount_fine', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('remaining_after', 15, 2)->default(0);
            $table->string('payment_method', 20)->default('cash');
            $table->unsignedBigInteger('handover_id')->nullable()->index(); // Relasi serah terima di Fase 4
            $table->enum('status', ['valid', 'cancellation_requested', 'cancelled'])->default('valid')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Pengajuan Pembatalan Pembayaran (Payment Cancellations)
        Schema::create('payment_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('requester_user_id')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_cancellations');
        Schema::dropIfExists('payments');
    }
};
