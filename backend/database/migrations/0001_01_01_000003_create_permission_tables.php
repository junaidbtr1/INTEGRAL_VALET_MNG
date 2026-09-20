<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $teamFK = $columnNames['team_foreign_key'];

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->string('group')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $teamFK) {
            $table->id();
            if ($teams) {
                $table->unsignedBigInteger($teamFK)->nullable()->index();
            }
            $table->string('name');
            $table->string('guard_name');
            $table->string('description')->nullable();
            $table->timestamps();
            if ($teams) {
                $table->unique([$teamFK, 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotPermission, $columnNames, $teams, $teamFK) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            if ($teams) {
                $table->unsignedBigInteger($teamFK)->default(0);
                $table->index($teamFK);
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type', $teamFK], 'mhp_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'mhp_primary');
            }
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $pivotRole, $columnNames, $teams, $teamFK) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            if ($teams) {
                $table->unsignedBigInteger($teamFK)->default(0);
                $table->index($teamFK);
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type', $teamFK], 'mhr_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'mhr_primary');
            }
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotPermission, $pivotRole) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotPermission, $pivotRole], 'rhp_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
