<?php

declare(strict_types=1);

namespace App\Listeners;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use jdavidbakr\MailTracker\Events\EmailSentEvent;

final class EmailSentListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EmailSentEvent $event): void
    {
        $sentEmail = $event->sent_email;

        Log::debug('EmailSentListener triggered', [
            'sent_email_id' => $sentEmail->id ?? 'N/A',
            'message_id'    => $sentEmail->message_id ?? 'N/A',
            'X-Model-ID'    => $sentEmail->getHeader('X-Model-ID') ?? 'N/A',
            'X-Model-Type'  => $sentEmail->getHeader('X-Model-Type') ?? 'N/A',
        ]);

        $modelId = $sentEmail->getHeader('X-Model-ID');
        $modelType = $sentEmail->getHeader('X-Model-Type');

        if ($modelId && $modelType) {
            $sentEmail->emailable_id = $modelId;
            $sentEmail->emailable_type = $modelType;
            $sentEmail->save();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(EmailSentEvent $event, Throwable $exception): void
    {
        $sentEmail = $event->sent_email;

        Log::error('EmailSentListener failed', [
            'exception_message' => $exception->getMessage(),
            'sent_email_id'     => $sentEmail->id ?? 'N/A',
            'message_id'        => $sentEmail->message_id ?? 'N/A',
            'X-Model-ID'        => $sentEmail->getHeader('X-Model-ID') ?? 'N/A',
            'X-Model-Type'      => $sentEmail->getHeader('X-Model-Type') ?? 'N/A',
        ]);
    }
}
