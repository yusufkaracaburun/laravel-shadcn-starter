<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

final class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        Vehicle::factory()
            ->count(20)
            ->create()
            ->each(function (Vehicle $vehicle) use ($users): void {
                $vehicle->drivers()->attach(
                    $users->random(rand(2, 5))->pluck('id')
                );
            });
    }
}
