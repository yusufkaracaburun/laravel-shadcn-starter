<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SentEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use jdavidbakr\MailTracker\Events\EmailSentEvent as BaseEmailSentEvent;

final class EmailSent extends BaseEmailSentEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public SentEmail $sentEmail)
    {
        parent::__construct($sentEmail);
        Log::debug('EmailSent event fired', ['sent_email_id' => $this->sentEmail->id]);
    }
}
