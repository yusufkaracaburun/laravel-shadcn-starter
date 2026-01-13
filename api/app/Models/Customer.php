<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerType;
use App\Enums\CustomerStatus;
use App\Helpers\AddressHelper;
use App\Observers\CustomerObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy([CustomerObserver::class])]
final class Customer extends BaseModel
{
    public static array $searchable = [
        'name',
        'email',
        'phone',
        'city',
        'primary_contact.email',
    ];

    protected $with = ['primaryContact'];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_customer')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function isBusiness(): bool
    {
        return $this->type === CustomerType::BUSINESS;
    }

    public function isPrivate(): bool
    {
        return $this->type === CustomerType::PRIVATE;
    }

    public function primaryContact(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_customer')
            ->wherePivot('is_primary', true)
            ->withPivot(['role', 'is_primary'])
            ->limit(1);
    }

    protected function casts(): array
    {
        return [
            'type'   => CustomerType::class,
            'status' => CustomerStatus::class,
        ];
    }

    protected function getPrimaryContactAttribute(): ?Contact
    {
        /** @var Contact|null $contact */
        $contact = $this->primaryContact()->first();

        return $contact;
    }

    protected function formattedAddress(): Attribute
    {
        return Attribute::make(
            get: fn (): array => AddressHelper::formatAddress(
                $this->address,
                $this->zipcode,
                $this->city,
            ),
        );
    }

    #[Scope]
    protected function business(Builder $query): Builder
    {
        return $query->where('type', CustomerType::BUSINESS);
    }

    #[Scope]
    protected function private(Builder $query): Builder
    {
        return $query->where('type', CustomerType::PRIVATE);
    }

    #[Scope]
    protected function withStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
