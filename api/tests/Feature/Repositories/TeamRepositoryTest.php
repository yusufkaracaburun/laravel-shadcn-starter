<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\TeamRepositoryInterface;

test('team repository can be resolved from container', function (): void {
    $repository = resolve(TeamRepositoryInterface::class);

    expect($repository)->toBeInstanceOf(TeamRepositoryInterface::class);
});

test('team repository can get all teams', function (): void {
    $repository = resolve(TeamRepositoryInterface::class);

    $teams = $repository->all();

    expect($teams)->toBeInstanceOf(Collection::class);
});

test('team repository can paginate teams', function (): void {
    $repository = resolve(TeamRepositoryInterface::class);

    $paginated = $repository->paginate(10);

    expect($paginated)->toBeInstanceOf(LengthAwarePaginator::class);
});

test('team repository can get teams for user', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    $repository = resolve(TeamRepositoryInterface::class);

    $teams = $repository->getOwnedTeamsForUser($user->id);

    expect($teams)->toBeInstanceOf(Collection::class);
    expect($teams->count())->toBeGreaterThan(0);
    expect($teams->first()->id)->toBe($team->id);
});

test('team repository can get personal team for user', function (): void {
    $user = User::factory()->create();
    $personalTeam = Team::factory()->create([
        'user_id' => $user->id,
        'personal_team' => true,
    ]);

    $repository = resolve(TeamRepositoryInterface::class);

    $team = $repository->getPersonalTeamForUser($user->id);

    expect($team)->toBeInstanceOf(Team::class);
    expect($team->id)->toBe($personalTeam->id);
    expect($team->personal_team)->toBeTrue();
});

test('team repository can create a team', function (): void {
    $user = User::factory()->create();

    $repository = resolve(TeamRepositoryInterface::class);

    $team = $repository->create([
        'name' => 'Test Team',
        'user_id' => $user->id,
        'personal_team' => false,
    ]);

    expect($team)->toBeInstanceOf(Team::class);
    expect($team->name)->toBe('Test Team');
    expect($team->user_id)->toBe($user->id);
});

test('team repository can find team by id', function (): void {
    $team = Team::factory()->create();

    $repository = resolve(TeamRepositoryInterface::class);

    $found = $repository->find($team->id);

    expect($found)->toBeInstanceOf(Team::class);
    expect($found->id)->toBe($team->id);
});
