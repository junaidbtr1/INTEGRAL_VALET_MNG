<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('plate_number');
            $table->string('vehicle_type');
            $table->string('color')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_blacklisted')->default(false);
            $table->string('blacklist_reason')->nullable();
            $table->string('photo_url')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('visit_count')->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'plate_number']);
            $table->index(['tenant_id', 'is_blacklisted']);
            $table->index(['tenant_id', 'is_vip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
