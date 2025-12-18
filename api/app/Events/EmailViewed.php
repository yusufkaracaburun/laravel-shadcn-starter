<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SentEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels; // Added for MailTracker
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets; // Added for MailTracker
use jdavidbakr\MailTracker\Events\ViewEmailEvent as BaseViewEmailEvent;

final class EmailViewed extends BaseViewEmailEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public SentEmail $sentEmail, public string $ipAddress)
    {
        parent::__construct($sentEmail, $ipAddress);
        Log::debug('EmailViewed event fired', ['sent_email_id' => $this->sentEmail->id, 'ip_address' => $this->ipAddress]);
    }
}
