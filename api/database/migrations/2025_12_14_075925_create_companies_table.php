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
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('industry');
            $table->string('status'); // active, inactive, pending
            $table->string('employees'); // 1-10, 11-50, 51-200, 201-500, 500+
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->timestamps();

            // Add indexes
            $table->index('email');
            $table->index('team_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
