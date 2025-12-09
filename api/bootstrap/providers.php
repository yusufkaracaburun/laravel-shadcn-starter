<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\MacroServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ServiceServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    MacroServiceProvider::class,
    FortifyServiceProvider::class,
    RepositoryServiceProvider::class,
    ServiceServiceProvider::class,
];
