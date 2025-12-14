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
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status'); // active, on-hold, completed, cancelled
            $table->string('priority'); // low, medium, high
            $table->string('category'); // design, development, marketing, support, other
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('progress')->default(0); // 0-100
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->timestamps();

            // Add indexes
            $table->index('team_id');
            $table->index('status');
            $table->index('priority');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
