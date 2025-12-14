<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot list companies', function (): void {
    /** @var TestCase $this */
    $response = $this->getJson('/api/company');

    $response->assertUnauthorized();
});

test('authenticated user can list companies', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $company = Company::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/company');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'code',
            'message',
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'industry', 'status', 'employees', 'team_id', 'created_at', 'updated_at'],
                ],
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
            'extra',
        ]);
});

test('companies list supports pagination', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    Company::factory()->count(15)->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/company?page=1&per_page=10');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data.data', 10)
                ->where('data.current_page', 1)
                ->where('data.per_page', 10)
                ->where('data.total', 15)
                ->etc()
        );
});

test('companies list is team-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $company1 = Company::factory()->create(['team_id' => $user1->current_team_id]);
    $company2 = Company::factory()->create(['team_id' => $user2->current_team_id]);

    Sanctum::actingAs($user1, ['*']);

    $response = $this->getJson('/api/company');

    $response->assertOk();

    $data = $response->json('data.data');
    $companyIds = collect($data)->pluck('id')->toArray();

    expect($companyIds)->toContain($company1->id);
    expect($companyIds)->not->toContain($company2->id);
});
