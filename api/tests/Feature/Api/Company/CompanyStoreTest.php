<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot create companies', function (): void {
    /** @var TestCase $this */
    $response = $this->postJson('/api/company', [
        'name'      => 'Test Company',
        'email'     => 'test@example.com',
        'industry'  => 'technology',
        'status'    => 'active',
        'employees' => '1-10',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can create new company', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $companyData = [
        'name'      => 'Test Company',
        'email'     => 'test@example.com',
        'phone'     => '+1234567890',
        'industry'  => 'technology',
        'status'    => 'active',
        'employees' => '1-10',
    ];

    $response = $this->postJson('/api/company', $companyData);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 201)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', $companyData['name'])
                    ->where('email', $companyData['email'])
                    ->where('industry', $companyData['industry'])
                    ->where('status', $companyData['status'])
                    ->where('employees', $companyData['employees'])
                    ->etc())
                ->has('extra')
                ->etc(),
        );

    $this->assertDatabaseHas('companies', [
        'email'   => $companyData['email'],
        'name'    => $companyData['name'],
        'team_id' => $user->current_team_id,
    ]);
});

test('company creation requires valid data', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/company', [
        'name'      => '',
        'email'     => 'invalid-email',
        'industry'  => 'invalid-industry',
        'status'    => 'invalid-status',
        'employees' => 'invalid-size',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'industry', 'status', 'employees']);
});

test('company creation requires unique email', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $existingCompany = Company::factory()->create(['email' => 'existing@example.com']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/company', [
        'name'      => 'Test Company',
        'email'     => 'existing@example.com',
        'industry'  => 'technology',
        'status'    => 'active',
        'employees' => '1-10',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('company can be created with optional phone', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/company', [
        'name'      => 'Test Company',
        'email'     => 'test@example.com',
        'industry'  => 'technology',
        'status'    => 'active',
        'employees' => '1-10',
    ]);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 201)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', 'Test Company')
                    ->where('email', 'test@example.com')
                    ->where('phone', null)
                    ->etc())
                ->etc(),
        );
});
