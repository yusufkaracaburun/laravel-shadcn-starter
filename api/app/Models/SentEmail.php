<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use jdavidbakr\MailTracker\Model\SentEmail as MailTrackerSentEmail;

final class SentEmail extends MailTrackerSentEmail
{
    public function emailable(): MorphTo
    {
        return $this->morphTo();
    }

    // You can add custom logic or override methods here
}
