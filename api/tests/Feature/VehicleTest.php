<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Enums\VehicleStatus;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

final class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_vehicles(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.vehicles.vehicles.index', ['per_page' => 10]));

        $response->assertStatus(200);
    }

    public function test_can_create_vehicle(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'make' => 'Test Make',
            'model' => 'Test Model',
            'year' => 2023,
            'license_plate' => 'TEST-' . rand(1000, 9999),
            'vin' => 'VIN-' . rand(1000, 9999),
            'status' => 'active',
        ];

        $response = $this->postJson(route('api.vehicles.vehicles.store'), $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.make', 'Test Make');
    }

    public function test_can_create_vehicle_with_drivers(): void
    {
        $user = User::factory()->create();
        $driver = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'make' => 'Test Make Driver',
            'model' => 'Test Model',
            'year' => 2023,
            'license_plate' => 'DRV-' . rand(1000, 9999),
            'vin' => 'VIN-DRV-' . rand(1000, 9999),
            'status' => 'active',
            'drivers' => [$driver->id],
        ];

        $response = $this->postJson(route('api.vehicles.vehicles.store'), $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.make', 'Test Make Driver');
        
        $this->assertTrue(Vehicle::where('license_plate', $data['license_plate'])->first()->drivers->contains($driver));
    }

    public function test_can_show_vehicle(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $vehicle = Vehicle::create([
             'make' => 'Test',
             'model' => 'Model',
             'year' => 2020,
             'license_plate' => 'SHOW-' . rand(1000,9999),
             'status' => VehicleStatus::ACTIVE,
        ]);

        $response = $this->getJson(route('api.vehicles.vehicles.show', $vehicle));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $vehicle->id);
    }

    public function test_can_update_vehicle(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $vehicle = Vehicle::create([
             'make' => 'Test',
             'model' => 'Model',
             'year' => 2020,
             'license_plate' => 'UPD-' . rand(1000,9999),
             'status' => VehicleStatus::ACTIVE,
        ]);

        $data = ['make' => 'Updated Make'];

        $response = $this->putJson(route('api.vehicles.vehicles.update', $vehicle), $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.make', 'Updated Make');
    }

    public function test_can_delete_vehicle(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $vehicle = Vehicle::create([
             'make' => 'Test',
             'model' => 'Model',
             'year' => 2020,
             'license_plate' => 'DEL-' . rand(1000,9999),
             'status' => VehicleStatus::ACTIVE,
        ]);

        $response = $this->deleteJson(route('api.vehicles.vehicles.destroy', $vehicle));

        $response->assertStatus(204);
        $this->assertNull(Vehicle::find($vehicle->id));
    }

    public function test_can_get_prerequisites(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.vehicles.vehicles.prerequisites'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'statuses' => [
                        '*' => ['label', 'value', 'color', 'style']
                    ]
                ]
            ]);
    }
}
