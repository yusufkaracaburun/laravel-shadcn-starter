<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;

test('team can be created with factory', function () {
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

test('team has fillable attributes', function () {
    // Arrange
    $team = new Team();

    // Act
    $fillable = $team->getFillable();

    // Assert
    expect($fillable)->toContain('name')
        ->toContain('personal_team');
});

test('team personal_team is cast to boolean', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $team = Team::create([
        'user_id' => $user->id,
        'name' => 'Personal Team',
        'personal_team' => 1,
    ]);

    // Assert
    expect($team->personal_team)->toBeTrue();
    expect($team->personal_team)->toBeBool();
});

test('team dispatches created event', function () {
    // Arrange
    Event::fake([TeamCreated::class]);
    $user = User::factory()->create();

    // Act
    Team::factory()->create([
        'user_id' => $user->id,
    ]);

    // Assert
    Event::assertDispatched(TeamCreated::class);
});

test('team dispatches updated event', function () {
    // Arrange
    Event::fake([TeamUpdated::class]);
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Act
    $team->update(['name' => 'Updated Team']);

    // Assert
    Event::assertDispatched(TeamUpdated::class);
});

test('team dispatches deleted event', function () {
    // Arrange
    Event::fake([TeamDeleted::class]);
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Act
    $team->delete();

    // Assert
    Event::assertDispatched(TeamDeleted::class);
});

test('team has team invitations relationship', function () {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Act
    $relationship = $team->teamInvitations();

    // Assert
    expect($relationship)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

