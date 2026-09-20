<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->restrictOnDelete();
            $table->foreignId('processed_by')->constrained('users')->restrictOnDelete();
            $table->string('receipt_number')->unique();

            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method');
            $table->string('status')->default('completed');

            $table->unsignedBigInteger('refund_of')->nullable();
            $table->string('refund_reason')->nullable();

            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('coupon_discount_amount')->default(0);

            $table->string('transaction_reference')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('refund_of')->references('id')->on('payments')->nullOnDelete();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
