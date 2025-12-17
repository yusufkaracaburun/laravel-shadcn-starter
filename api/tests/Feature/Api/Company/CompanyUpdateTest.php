<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Company;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot update companies', function (): void {
    /** @var TestCase $this */
    $company = Company::factory()->create();

    $response = $this->putJson("/api/company/{$company->id}", [
        'name' => 'Updated Company',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can update company', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $company = Company::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $updateData = [
        'name'   => 'Updated Company Name',
        'status' => 'inactive',
    ];

    $response = $this->putJson("/api/company/{$company->id}", $updateData);

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', $updateData['name'])
                    ->where('status', $updateData['status'])
                    ->etc())
                ->etc(),
        );

    $this->assertDatabaseHas('companies', [
        'id'     => $company->id,
        'name'   => $updateData['name'],
        'status' => $updateData['status'],
    ]);
});

test('company update requires valid data', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $company = Company::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/company/{$company->id}", [
        'email'    => 'invalid-email',
        'industry' => 'invalid-industry',
        'status'   => 'invalid-status',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'industry', 'status']);
});

test('company update requires unique email', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $company1 = Company::factory()->create(['team_id' => $user->current_team_id]);
    $company2 = Company::factory()->create(['email' => 'existing@example.com', 'team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/company/{$company1->id}", [
        'email' => 'existing@example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('company update allows same email for same company', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $company = Company::factory()->create(['email' => 'test@example.com', 'team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/company/{$company->id}", [
        'name'  => 'Updated Name',
        'email' => 'test@example.com', // Same email should be allowed
    ]);

    $response->assertOk();
});

test('company update is team-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create teams for users
    $team1 = Team::factory()->create(['user_id' => $user1->id]);
    $team2 = Team::factory()->create(['user_id' => $user2->id]);

    $user1->update(['current_team_id' => $team1->id]);
    $user2->update(['current_team_id' => $team2->id]);

    $company = Company::factory()->create(['team_id' => $team1->id]);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->putJson("/api/company/{$company->id}", [
        'name' => 'Updated Name',
    ]);

    // Should return 404 or 403 because company belongs to different team
    expect($response->status())->toBeIn([404, 403]);
});
