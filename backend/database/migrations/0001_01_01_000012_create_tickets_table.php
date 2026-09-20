<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number')->unique();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parking_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->string('vehicle_plate');
            $table->string('vehicle_type');
            $table->string('vehicle_color')->nullable();

            $table->string('status')->default('created');

            $table->timestamp('entry_at');
            $table->timestamp('exit_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();

            $table->unsignedBigInteger('base_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('surcharge_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);

            $table->string('qr_code_url')->nullable();
            $table->string('barcode_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'vehicle_plate']);
        });

        // Add FK on parking_slots after tickets exists
        Schema::table('parking_slots', function (Blueprint $table) {
            $table->foreign('current_ticket_id')->references('id')->on('tickets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parking_slots', function (Blueprint $table) {
            $table->dropForeign(['current_ticket_id']);
        });
        Schema::dropIfExists('tickets');
    }
};
