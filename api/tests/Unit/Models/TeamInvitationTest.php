<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

test('team invitation can be created', function () {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Act
    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
    ]);

    // Assert
    expect($invitation)->toBeInstanceOf(TeamInvitation::class);
    expect($invitation->team_id)->toBe($team->id);
    expect($invitation->email)->toBe('invitee@example.com');
    expect($invitation->role)->toBe('member');
});

test('team invitation has team relationship', function () {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $invitation = TeamInvitation::create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
    ]);

    // Act
    $relationship = $invitation->team();

    // Assert
    expect($relationship)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($invitation->team)->toBeInstanceOf(Team::class);
    expect($invitation->team->id)->toBe($team->id);
});

test('team invitation has fillable attributes', function () {
    // Arrange
    $invitation = new TeamInvitation();

    // Act
    $fillable = $invitation->getFillable();

    // Assert
    expect($fillable)->toContain('email')
        ->toContain('role');
});

