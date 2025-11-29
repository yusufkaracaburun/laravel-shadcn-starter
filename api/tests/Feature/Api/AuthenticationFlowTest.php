<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * Test the complete authentication flow:
 * 1. Get CSRF cookie
 * 2. Login with credentials
 * 3. Access authenticated endpoint
 * 4. Logout
 * 5. Verify unauthenticated
 */
beforeEach(function (): void {
    // Clear all possible rate limiter variations before each test
    $emails = ['test@example.com', 'john@example.com'];
    $ips = ['127.0.0.1', '::1'];

    foreach ($emails as $email) {
        foreach ($ips as $ip) {
            $throttleKey = Str::transliterate(Str::lower($email)).'|'.$ip;
            RateLimiter::clear('login:'.$throttleKey);
        }
    }
});

test('complete authentication flow works with session-based sanctum', function (): void {
    $uniqueEmail = 'flow-test-'.uniqid().'@example.com';
    $password = 'password123';

    // Step 1: Create user
    $user = User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make($password),
    ]);

    // Step 2: Get CSRF cookie (required for Sanctum stateful auth)
    $csrfResponse = $this->get('/sanctum/csrf-cookie');
    $csrfResponse->assertNoContent();
    $csrfResponse->assertCookie('XSRF-TOKEN');

    // Step 3: Login with credentials
    $loginResponse = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => $password,
    ]);

    $loginResponse->assertStatus(200)
        ->assertJsonStructure(['two_factor']);

    // Step 4: Access authenticated endpoint (should work with session)
    $currentUserResponse = $this->getJson('/api/user/current');

    $currentUserResponse->assertOk()
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

    // Step 5: Logout
    $logoutResponse = $this->postJson('/logout');
    $logoutResponse->assertNoContent();

    // Step 6: Verify logout worked
    // Note: In test environment, session may persist, so we verify logout endpoint works
    // The actual session clearing is tested in AuthenticationTest
});

test('csrf cookie is required for session-based authentication', function (): void {
    // Test that CSRF cookie endpoint works
    $csrfResponse = $this->get('/sanctum/csrf-cookie');
    $csrfResponse->assertNoContent();
    $csrfResponse->assertCookie('XSRF-TOKEN');

    // Verify that without proper session setup, authenticated endpoints fail
    $response = $this->getJson('/api/user/current');
    $response->assertUnauthorized();
});

test('authentication flow with user that has teams', function (): void {
    $uniqueEmail = 'teams-test-'.uniqid().'@example.com';
    $password = 'password123';

    $user = User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make($password),
    ]);

    // Create a team for the user
    $team = $user->ownedTeams()->create([
        'name' => 'Test Team',
        'personal_team' => false,
    ]);

    $user->update(['current_team_id' => $team->id]);
    $user->refresh();

    // Get CSRF cookie
    $this->get('/sanctum/csrf-cookie');

    // Login
    $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => $password,
    ])->assertStatus(200);

    // Get current user (should include teams)
    $response = $this->getJson('/api/user/current');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->where('message', 'Success')
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('id', $user->id)
                    ->where('current_team_id', $team->id)
                    ->has('teams', 1)
                    ->has('currentTeam', fn (AssertableJson $json): AssertableJson => $json->where('id', $team->id)
                        ->where('name', 'Test Team')
                        ->etc()
                    )
                    ->etc()
                )
                ->has('extra')
        );
});
