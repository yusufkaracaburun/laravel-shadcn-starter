<?php

declare(strict_types=1);

namespace App\Http\Resources;

final class InvoiceCollection extends BaseCollection
{
    protected string $resourceClass = InvoiceResource::class;
}
