<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('floor')->default('G');
            $table->string('zone')->default('A');
            $table->string('slot_number');
            $table->string('slot_type')->default('standard');
            $table->string('status')->default('available');
            $table->json('vehicle_types_allowed')->nullable();
            $table->boolean('is_covered')->default(false);
            $table->unsignedBigInteger('current_ticket_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'floor', 'zone', 'slot_number']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_slots');
    }
};
