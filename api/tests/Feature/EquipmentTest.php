<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_equipments(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Equipment::factory(5)->create();

        $response = $this->getJson(route('api.equipments.equipments.index', ['per_page' => 10]));

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_create_equipment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'name'          => 'Test Drill',
            'serial_number' => 'SN-' . rand(1000, 9999),
            'type'          => 'Drill',
            'status'        => 'active',
        ];

        $response = $this->postJson(route('api.equipments.equipments.store'), $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Drill');
    }

    public function test_can_show_equipment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $equipment = Equipment::factory()->create();

        $response = $this->getJson(route('api.equipments.equipments.show', $equipment));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $equipment->id);
    }

    public function test_can_update_equipment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $equipment = Equipment::factory()->create();

        $data = ['name' => 'Updated Drill'];

        $response = $this->putJson(route('api.equipments.equipments.update', $equipment), $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Drill');
    }

    public function test_can_delete_equipment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $equipment = Equipment::factory()->create();

        $response = $this->deleteJson(route('api.equipments.equipments.destroy', $equipment));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('equipments', ['id' => $equipment->id]);
    }

    public function test_can_get_prerequisites(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.equipments.prerequisites'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'statuses' => [
                        '*' => ['label', 'value', 'color', 'style'],
                    ],
                ],
            ]);
    }
}
