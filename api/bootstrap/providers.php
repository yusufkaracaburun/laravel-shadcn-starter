<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    // Note: TelescopeServiceProvider is registered conditionally in AppServiceProvider::register()
    // only when environment is 'local' - it should NOT be listed here to avoid production registration
];
