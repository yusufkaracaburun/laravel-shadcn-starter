<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Laravel\Sanctum\Sanctum;

test('unauthenticated users cannot delete companies', function (): void {
    /** @var TestCase $this */
    $company = Company::factory()->create();

    $response = $this->deleteJson("/api/company/{$company->id}");

    $response->assertUnauthorized();
});

test('authenticated user can delete company', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $company = Company::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson("/api/company/{$company->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('companies', [
        'id' => $company->id,
    ]);
});

test('company deletion is team-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $company = Company::factory()->create(['team_id' => $user1->current_team_id]);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->deleteJson("/api/company/{$company->id}");

    // Should return 404 or 403 because company belongs to different team
    expect($response->status())->toBeIn([404, 403]);

    // Company should still exist
    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
    ]);
});
