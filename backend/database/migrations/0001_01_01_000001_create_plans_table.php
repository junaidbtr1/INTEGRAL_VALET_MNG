<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plans must be created BEFORE tenants references it
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_monthly');
            $table->unsignedBigInteger('price_yearly');
            $table->string('currency', 3)->default('USD');

            $table->unsignedInteger('max_slots')->default(50);
            $table->unsignedInteger('max_staff_users')->default(5);
            $table->unsignedInteger('max_tickets_per_day')->default(200);

            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });
        Schema::dropIfExists('plans');
    }
};
