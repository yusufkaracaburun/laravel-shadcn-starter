<?php

declare(strict_types=1);

use App\Enums\VehicleStatus;
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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('make'); // e.g. Toyota
            $table->string('model'); // e.g. Corolla
            $table->integer('year');
            $table->string('license_plate')->unique();
            $table->string('vin')->unique()->nullable();
            $table->enum('status', VehicleStatus::values())->default(VehicleStatus::ACTIVE->value);
            $table->date('inspection_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
