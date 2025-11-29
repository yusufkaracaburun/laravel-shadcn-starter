<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

test('user can be assigned a role', function (): void {
    // Arrange
    $user = User::factory()->create();
    $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);

    // Act
    $user->assignRole($role);

    // Assert
    expect($user->hasRole('test-role'))->toBeTrue();
    expect($user->roles)->toHaveCount(1);
});

test('user can be assigned a permission', function (): void {
    // Arrange
    $user = User::factory()->create();
    $permission = Permission::create(['name' => 'test.permission', 'guard_name' => 'web']);

    // Act
    $user->givePermissionTo($permission);

    // Assert
    expect($user->hasPermissionTo('test.permission'))->toBeTrue();
    expect($user->permissions)->toHaveCount(1);
});

test('user can check permission via role', function (): void {
    // Arrange
    $user = User::factory()->create();
    $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'test.permission', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    // Act & Assert
    expect($user->hasPermissionTo('test.permission'))->toBeTrue();
    expect($user->can('test.permission'))->toBeTrue();
});

test('user can have team-scoped role', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $role = Role::create(['name' => 'team-admin', 'guard_name' => 'web']);

    // Act - Set team context via PermissionRegistrar and assign role
    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($team->id);

    $user->assignRole($role);
    $permissionRegistrar->setPermissionsTeamId(null); // Reset to global context

    // Assert - Check with team context
    $permissionRegistrar->setPermissionsTeamId($team->id);

    $user->unsetRelation('roles'); // Clear cached relation
    expect($user->hasRole($role))->toBeTrue();

    // Check without team context (should not have the role globally)
    $permissionRegistrar->setPermissionsTeamId(null);
    $user->unsetRelation('roles'); // Clear cached relation
    expect($user->hasRole($role))->toBeFalse(); // Global role should not exist
});

test('user can check team-scoped permission', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $role = Role::create(['name' => 'team-admin', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'teams.manage', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    // Act - Set team context via PermissionRegistrar and assign role
    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($team->id);

    $user->assignRole($role);
    $permissionRegistrar->setPermissionsTeamId(null);

    // Assert - Check with team context
    $permissionRegistrar->setPermissionsTeamId($team->id);

    expect($user->hasPermissionTo('teams.manage'))->toBeTrue();
    $permissionRegistrar->setPermissionsTeamId(null);
});

test('role can have multiple permissions', function (): void {
    // Arrange
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $permission1 = Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'users.create', 'guard_name' => 'web']);

    // Act
    $role->givePermissionTo([$permission1, $permission2]);

    // Assert
    expect($role->permissions)->toHaveCount(2);
    expect($role->hasPermissionTo('users.view'))->toBeTrue();
    expect($role->hasPermissionTo('users.create'))->toBeTrue();
});
