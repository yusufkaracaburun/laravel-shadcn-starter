<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot view company', function (): void {
    /** @var TestCase $this */
    $company = Company::factory()->create();

    $response = $this->getJson("/api/company/{$company->id}");

    $response->assertUnauthorized();
});

test('authenticated user can view company', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $company = Company::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/company/{$company->id}");

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('id', $company->id)
                    ->where('name', $company->name)
                    ->where('email', $company->email)
                    ->has('created_at')
                    ->has('updated_at')
                    ->etc())
                ->has('extra')
                ->etc()
        );
});

test('company show returns 404 for non-existent company', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/company/999999');

    $response->assertNotFound();
});

test('company show is team-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $company = Company::factory()->create(['team_id' => $user1->current_team_id]);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->getJson("/api/company/{$company->id}");

    // Should return 404 because company belongs to different team
    $response->assertNotFound();
});
