<?php

declare(strict_types=1);

namespace App\Http\Resources\Contacts;

use App\Http\Resources\BaseCollection;
use App\Http\Resources\ContactResource;

final class ContactCollection extends BaseCollection
{
    protected string $resourceClass = ContactResource::class;
}
