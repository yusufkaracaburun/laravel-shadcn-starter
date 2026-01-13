<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Teams\TeamResource;
use App\Http\Resources\Teams\TeamCollection;

interface TeamServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): TeamCollection;

    public function getAll(array $columns = ['*']): TeamCollection;

    public function findById(int $id): TeamResource;

    public function createTeam(array $data): TeamResource;

    public function updateTeam(Team $team, array $data): TeamResource;

    public function deleteTeam(Team $team): bool;
}
