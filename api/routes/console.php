<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use App\Events\ExampleBroadcastEvent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reverb:broadcast {message?}', function (?string $message = null) {
    $message = $message ?? 'Hello from Reverb! This is a test message.';

    $event = new ExampleBroadcastEvent($message);

    $this->info("📡 Broadcasting event to 'example-channel'...");
    $this->info('   Event name: example.event');
    $this->info("   Message: {$message}");

    event($event);

    $this->info('✅ Event dispatched! Check your Reverb server logs and client console.');
})->purpose('Broadcast a test event to the example-channel via Reverb');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled tasks. These tasks will be
| executed by Laravel's task scheduler on a daily basis.
|
*/

Schedule::daily()
    ->onOneServer()
    ->group(fn () => [
        // Add scheduled commands here
    ]);
