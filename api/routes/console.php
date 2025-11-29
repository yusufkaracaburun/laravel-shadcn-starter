<?php

declare(strict_types=1);

use App\Events\ExampleBroadcastEvent;
use App\Events\NotificationCreated;
use Illuminate\Foundation\Inspiring;
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

    $this->info('📡 Broadcasting event to "example-channel" with message: '.($message ?? 'Hello from Reverb! This is a test message.'));
    event(new ExampleBroadcastEvent($message ?? 'Hello from Reverb! This is a test message.'));

    $this->info('📡 Broadcasting notification to user 1 with message: '.($message ?? 'Your order has been shipped!'));
    event(new NotificationCreated(
        userId: 1,
        message: $message ?? 'Your order has been shipped!',
        type: 'success',
        title: 'Order Update'
    ));

    $this->info('📡 Broadcasting public notification with message: '.($message ?? 'System maintenance in 5 minutes'));
    event(new NotificationCreated(
        message: $message ?? 'System maintenance in 5 minutes',
        type: 'warning',
        isPublic: true
    ));

    $this->info('📡 Broadcasting notification to user 1 with message: '.($message ?? 'New message received'));
    event(new NotificationCreated(
        userId: 1,
        message: $message ?? 'New message received',
        type: 'info',
        title: 'New Message',
        description: 'You have a new message from John',
        data: ['message_id' => 123]
    ));

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
