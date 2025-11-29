<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('team can be created with factory', function (): void {
    // Arrange
    $user = User::factory()->create();

    // Act
    $team = Team::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Team',
        'personal_team' => false,
    ]);

    // Assert
    expect($team)->toBeInstanceOf(Team::class);
    expect($team->name)->toBe('Test Team');
    expect($team->personal_team)->toBeFalse();
    expect($team->user_id)->toBe($user->id);
});

test('team has fillable attributes', function (): void {
    // Arrange
    $team = new Team();

    // Act
    $fillable = $team->getFillable();

    // Assert
    expect($fillable)->toContain('name')
        ->toContain('personal_team')
        ->toContain('user_id');
});

test('team personal_team is cast to boolean', function (): void {
    // Arrange
    $user = User::factory()->create();

    // Act
    $team = Team::query()->create([
        'user_id' => $user->id,
        'name' => 'Personal Team',
        'personal_team' => 1,
    ]);

    // Assert
    expect($team->personal_team)->toBeTrue();
    expect($team->personal_team)->toBeBool();
});

test('team has owner relationship', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Act
    $owner = $team->owner;

    // Assert
    expect($owner)->toBeInstanceOf(User::class);
    expect($owner->id)->toBe($user->id);
});

test('team has users relationship', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $team->users()->attach($user->id, ['role' => 'member']);

    // Act
    $users = $team->users;

    // Assert
    expect($users)->toHaveCount(1);
    expect($users->first()->id)->toBe($user->id);
});

test('team has team invitations relationship', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Act
    $relationship = $team->teamInvitations();

    // Assert
    expect($relationship)->toBeInstanceOf(HasMany::class);
});
