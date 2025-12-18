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
    FortifyServiceProvider::class,
    MacroServiceProvider::class,
    RepositoryServiceProvider::class,
    ServiceServiceProvider::class,
];
