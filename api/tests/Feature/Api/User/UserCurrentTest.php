<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot access current user endpoint', function (): void {
    /** @var TestCase $this */
    $response = $this->getJson('/api/user/current');

    $response->assertUnauthorized();
});

test('authenticated user can access current user endpoint', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user/current');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'code',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'current_team_id',
                'created_at',
                'updated_at',
                'teams',
                'currentTeam',
            ],
        ])
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->where('message', 'Success')
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('id', $user->id)
                    ->where('email', $user->email)
                    ->where('name', $user->name)
                    ->whereType('teams', 'array')
                    ->whereType('currentTeam', ['array', 'null'])
                    ->etc()
                )
                ->has('extra')
        );
});
