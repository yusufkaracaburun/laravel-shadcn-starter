<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        // Make team_id nullable in model_has_roles
        if (Schema::hasTable($tableNames['model_has_roles'])) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey, $pivotRole, $modelMorphKey): void {
                // Drop the existing primary key
                $table->dropPrimary('model_has_roles_role_model_type_primary');

                // Make team_id nullable
                $table->unsignedBigInteger($teamForeignKey)->nullable()->change();

                // Recreate primary key without team_id (since it's now nullable)
                $table->primary([$pivotRole, $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
            });
        }

        // Make team_id nullable in model_has_permissions
        if (Schema::hasTable($tableNames['model_has_permissions'])) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey, $pivotPermission, $modelMorphKey): void {
                // Drop the existing primary key
                $table->dropPrimary('model_has_permissions_permission_model_type_primary');

                // Make team_id nullable
                $table->unsignedBigInteger($teamForeignKey)->nullable()->change();

                // Recreate primary key without team_id (since it's now nullable)
                $table->primary([$pivotPermission, $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We can't easily reverse this without data loss
        // In production, you'd need to handle this carefully
    }
};
