<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

final class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vehicle::factory()->count(20)->create();
    }
}
