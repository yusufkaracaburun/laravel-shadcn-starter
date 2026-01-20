<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_customers(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Customer::factory(5)->create();

        $response = $this->getJson(route('api.customers.customers.index', ['per_page' => 10]));

        $response->assertStatus(200);
    }

    public function test_can_create_customer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'name'   => 'Test Customer',
            'email'  => 'test@example.com',
            'phone'  => '+31612345678',
            'status' => 'active',
        ];

        $response = $this->postJson(route('api.customers.customers.store'), $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Customer');
    }

    public function test_can_create_business_customer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'name'       => 'Test Business',
            'email'      => 'business@example.com',
            'phone'      => '+31612345678',
            'type'       => CustomerType::BUSINESS->value,
            'kvk_number' => '12345678',
            'vat_number' => 'NL123456789B01',
            'status'     => 'active',
        ];

        $response = $this->postJson(route('api.customers.customers.store'), $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Business')
            ->assertJsonPath('data.type', CustomerType::BUSINESS->value);
    }

    public function test_can_show_customer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create();

        $response = $this->getJson(route('api.customers.customers.show', $customer));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $customer->id);
    }

    public function test_can_update_customer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create([
            'name'   => 'Original Name',
            'status' => CustomerStatus::ACTIVE,
        ]);

        $data = ['name' => 'Updated Name'];

        $response = $this->putJson(route('api.customers.customers.update', $customer), $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_can_delete_customer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create();

        $response = $this->deleteJson(route('api.customers.customers.destroy', $customer));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
