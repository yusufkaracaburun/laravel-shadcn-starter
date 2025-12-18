<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EmailSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

final class EmailSentListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EmailSent $event): void
    {
        $sentEmail = $event->sent_email;

        $modelId = $sentEmail->getHeader('X-Model-ID');
        $modelType = $sentEmail->getHeader('X-Model-Type');

        if ($modelId && $modelType) {
            $sentEmail->emailable_id = $modelId;
            $sentEmail->emailable_type = $modelType;
            $sentEmail->save();
        }
    }
}
