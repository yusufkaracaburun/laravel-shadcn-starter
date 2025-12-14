<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\TeamServiceInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;

test('team service can be resolved from container', function (): void {
    $service = resolve(TeamServiceInterface::class);

    expect($service)->toBeInstanceOf(TeamServiceInterface::class);
});

test('team service can get all teams', function (): void {
    $service = resolve(TeamServiceInterface::class);

    $teams = $service->all();

    expect($teams)->toBeInstanceOf(Collection::class);
});

test('team service can paginate teams', function (): void {
    $service = resolve(TeamServiceInterface::class);

    $paginated = $service->paginate(10);

    expect($paginated)->toBeInstanceOf(LengthAwarePaginator::class);
});

test('team service can find team by id', function (): void {
    $team = Team::factory()->create();

    $service = resolve(TeamServiceInterface::class);

    $found = $service->find($team->id);

    expect($found)->toBeInstanceOf(Team::class);
    expect($found->id)->toBe($team->id);
});

test('team service can create a team', function (): void {
    $user = User::factory()->create();

    $service = resolve(TeamServiceInterface::class);

    $team = $service->create([
        'name' => 'Test Team',
        'user_id' => $user->id,
        'personal_team' => false,
    ]);

    expect($team)->toBeInstanceOf(Team::class);
    expect($team->name)->toBe('Test Team');
    expect($team->user_id)->toBe($user->id);
});

test('team service can update a team', function (): void {
    $team = Team::factory()->create();

    $service = resolve(TeamServiceInterface::class);

    $updated = $service->update($team->id, [
        'name' => 'Updated Team Name',
    ]);

    expect($updated)->toBeInstanceOf(Team::class);
    expect($updated->name)->toBe('Updated Team Name');
});

test('team service can delete a team', function (): void {
    $team = Team::factory()->create();

    $service = resolve(TeamServiceInterface::class);

    $deleted = $service->delete($team->id);

    expect($deleted)->toBeTrue();
    expect($service->exists($team->id))->toBeFalse();
});

test('team service can check if team exists', function (): void {
    $team = Team::factory()->create();

    $service = resolve(TeamServiceInterface::class);

    expect($service->exists($team->id))->toBeTrue();
    expect($service->exists(99999))->toBeFalse();
});

test('team service has repository injected', function (): void {
    $service = resolve(TeamServiceInterface::class);

    $repository = $service->getRepository();

    expect($repository)->toBeInstanceOf(TeamRepositoryInterface::class);
});
