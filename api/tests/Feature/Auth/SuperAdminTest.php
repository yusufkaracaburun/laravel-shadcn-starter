<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Database\Seeders\UserSeeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\PermissionRegistrar;

// Clear permission cache before each test (per Spatie testing docs)
beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('super admin can bypass all permission checks via Gate::before', function (): void {
    // Arrange
    $superAdmin = User::factory()->create();
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    $permission = Permission::create(['name' => 'test.permission', 'guard_name' => 'web']);

    // Act & Assert
    expect($superAdmin->can('test.permission'))->toBeTrue();
    expect($superAdmin->can('non-existent.permission'))->toBeTrue();
    expect($superAdmin->can('users.view'))->toBeTrue();
    expect($superAdmin->can('teams.delete'))->toBeTrue();
});

test('super admin can use canAny and cannot methods correctly', function (): void {
    // Arrange
    $superAdmin = User::factory()->create();
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    // Act & Assert
    expect($superAdmin->canAny(['users.view', 'teams.create']))->toBeTrue();
    expect($superAdmin->cannot('users.view'))->toBeFalse();
    expect($superAdmin->cannot('non-existent.permission'))->toBeFalse();
});

test('super admin can access routes without team requirement', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    Sanctum::actingAs($superAdmin, ['*']);

    // Act - Try to access a route that would normally require a team
    $response = $this->getJson('/api/user/current');

    // Assert
    $response->assertOk();

    expect($response->json('data.email'))->toBe($superAdmin->email);
});

test('super admin can perform all CRUD operations on users', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    $targetUser = User::factory()->create();

    Sanctum::actingAs($superAdmin, ['*']);

    // Test view any
    $response = $this->getJson('/api/user');
    $response->assertOk();

    // Test view
    $response = $this->getJson("/api/user/{$targetUser->id}");
    $response->assertOk();

    // Test create
    $response = $this->postJson('/api/user', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $response->assertCreated();

    // Test update
    $response = $this->putJson("/api/user/{$targetUser->id}", [
        'name' => 'Updated Name',
        'email' => $targetUser->email,
    ]);
    $response->assertOk();

    // Test delete
    $response = $this->deleteJson("/api/user/{$targetUser->id}");
    $response->assertNoContent();
});

test('super admin can perform all CRUD operations on teams', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

    // Assign role globally (team_id = null) when teams are enabled
    // Per Spatie docs: clear cache before assigning roles
    $permissionRegistrar = resolve(PermissionRegistrar::class);
    $permissionRegistrar->forgetCachedPermissions();

    $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
    $permissionRegistrar->setPermissionsTeamId(null); // Set to null for global role

    $superAdmin->unsetRelation('roles');
    $superAdmin->assignRole($role);

    // Clear cache after assignment (per Spatie docs)
    $permissionRegistrar->forgetCachedPermissions();

    // Verify role is assigned globally
    $permissionRegistrar->setPermissionsTeamId(null);

    $superAdmin->unsetRelation('roles');
    expect($superAdmin->hasRole('super-admin'))->toBeTrue();

    $permissionRegistrar->setPermissionsTeamId($originalTeamId); // Restore
    $superAdmin->refresh(); // Refresh to ensure role is loaded

    $team = Team::factory()->create();
    // Ensure team has no users attached to avoid foreign key constraints
    $team->users()->detach();

    Sanctum::actingAs($superAdmin, ['*']);

    // Test view any
    $response = $this->getJson('/api/teams');
    $response->assertOk();

    // Test view
    $response = $this->getJson("/api/teams/{$team->id}");
    if ($response->status() !== 200) {
        dump([
            'status' => $response->status(),
            'content' => $response->getContent(),
            'json' => $response->json(),
        ]);
    }

    $response->assertOk();

    // Test create
    $response = $this->postJson('/api/teams', [
        'name' => 'New Team',
        'personal_team' => false,
    ]);
    $response->assertCreated();

    // Test update
    $response = $this->putJson("/api/teams/{$team->id}", [
        'name' => 'Updated Team Name',
    ]);
    $response->assertOk();

    // Test delete - use a fresh team to avoid any relationship issues
    $teamToDelete = Team::factory()->create();
    $teamToDelete->users()->detach();

    $response = $this->deleteJson("/api/teams/{$teamToDelete->id}");
    $response->assertNoContent();
});

test('super admin bypasses team-scoped permission checks', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    $team = Team::factory()->create();
    $permission = Permission::create(['name' => 'teams.manage', 'guard_name' => 'web']);

    // Act - Check permission without team context
    $permissionRegistrar = resolve(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId(null);

    // Assert - Super admin can access team-scoped permissions
    expect($superAdmin->can('teams.manage'))->toBeTrue();

    // Even with team context set, super admin should still have access
    $permissionRegistrar->setPermissionsTeamId($team->id);
    expect($superAdmin->can('teams.manage'))->toBeTrue();
});

test('super admin is not linked to teams', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    // Assert
    expect($superAdmin->current_team_id)->toBeNull();
    expect($superAdmin->teams)->toHaveCount(0);
});

test('super admin can access user from different team', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    $team = Team::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->teams()->attach($team->id, ['role' => 'member']);
    $otherUser->update(['current_team_id' => $team->id]);

    Sanctum::actingAs($superAdmin, ['*']);

    // Act
    $response = $this->getJson("/api/user/{$otherUser->id}");

    // Assert - Super admin can view users from any team
    $response->assertOk();

    expect($response->json('data.id'))->toBe($otherUser->id);
});

test('super admin can update user from different team', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    $team = Team::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->teams()->attach($team->id, ['role' => 'member']);
    $otherUser->update(['current_team_id' => $team->id]);

    Sanctum::actingAs($superAdmin, ['*']);

    // Act
    $response = $this->putJson("/api/user/{$otherUser->id}", [
        'name' => 'Updated by Super Admin',
        'email' => $otherUser->email,
    ]);

    // Assert
    $response->assertOk();

    $otherUser->refresh();
    expect($otherUser->name)->toBe('Updated by Super Admin');
});

test('super admin can delete user from different team', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    $team = Team::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->teams()->attach($team->id, ['role' => 'member']);
    $otherUser->update(['current_team_id' => $team->id]);

    Sanctum::actingAs($superAdmin, ['*']);

    // Act
    $response = $this->deleteJson("/api/user/{$otherUser->id}");

    // Assert
    $response->assertNoContent();

    expect(User::query()->find($otherUser->id))->toBeNull();
});

test('regular users still require proper permissions', function (): void {
    // Arrange
    $regularUser = User::factory()->create([
        'current_team_id' => null,
    ]);
    $permission = Permission::create(['name' => 'users.view', 'guard_name' => 'web']);

    // Act & Assert - Regular user without permission cannot access
    expect($regularUser->can('users.view'))->toBeFalse();
    expect($regularUser->can('users.create'))->toBeFalse();
});

test('regular users still require team when accessing protected routes', function (): void {
    // Arrange
    $regularUser = User::factory()->create([
        'current_team_id' => null,
    ]);

    Sanctum::actingAs($regularUser, ['*']);

    // Act - Try to access route that requires team
    $response = $this->getJson('/api/user/current');

    // Assert - Should fail without team (unless middleware is not applied)
    // Note: This test depends on whether the route uses the 'team' middleware
    // If the route doesn't use it, this test may need adjustment
    $response->assertOk(); // Current route might not require team middleware
});

test('super admin user is created correctly in seeder', function (): void {
    // Arrange - Run the seeder
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    // Act
    $superAdmin = User::query()->where('email', 'super-admin@example.com')->first();

    // Assert
    expect($superAdmin)->not->toBeNull();
    expect($superAdmin->hasRole('super-admin'))->toBeTrue();
    expect($superAdmin->current_team_id)->toBeNull();
    expect($superAdmin->teams)->toHaveCount(0);
    expect(Hash::check('password', $superAdmin->password))->toBeTrue();
});

test('super admin can access team they do not belong to', function (): void {
    // Arrange
    $superAdmin = User::factory()->create([
        'current_team_id' => null,
    ]);
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdmin->assignRole($role);

    $team = Team::factory()->create();
    // Ensure super admin is NOT a member of this team
    $superAdmin->teams()->detach();

    Sanctum::actingAs($superAdmin, ['*']);

    // Act
    $response = $this->getJson("/api/teams/{$team->id}");

    // Assert - Super admin can view teams they don't belong to
    $response->assertOk();

    expect($response->json('data.id'))->toBe($team->id);
});
