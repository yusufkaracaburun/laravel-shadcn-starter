<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

final class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Equipment::factory(10)->create();
    }
}
