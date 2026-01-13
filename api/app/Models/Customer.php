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

/**
 * Customer model.
 *
 * Represents a customer (business or private) in the system.
 */
#[ObservedBy([CustomerObserver::class])]
final class Customer extends BaseModel
{
    /**
     * Searchable fields for this model.
     *
     * @var list<string>
     */
    public static array $searchable = [
        'name',
        'email',
        'phone',
        'city',
        'primary_contact.email',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var list<string>
     */
    protected $with = ['primaryContact'];

    /**
     * Get all payments for this customer.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all invoices for this customer.
     *
     * @return HasMany<Invoice>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all contacts associated with this customer.
     *
     * @return BelongsToMany<Contact>
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_customer')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Check if the customer is a business.
     */
    public function isBusiness(): bool
    {
        return $this->type === CustomerType::BUSINESS;
    }

    /**
     * Check if the customer is private.
     */
    public function isPrivate(): bool
    {
        return $this->type === CustomerType::PRIVATE;
    }

    /**
     * Get the primary contact relationship.
     *
     * @return BelongsToMany<Contact>
     */
    public function primaryContact(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_customer')
            ->wherePivot('is_primary', true)
            ->withPivot(['role', 'is_primary'])
            ->limit(1);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'   => CustomerType::class,
            'status' => CustomerStatus::class,
        ];
    }

    /**
     * Get the primary contact attribute.
     */
    protected function getPrimaryContactAttribute(): ?Contact
    {
        /** @var Contact|null $contact */
        $contact = $this->primaryContact()->first();

        return $contact;
    }

    /**
     * Get the formatted address attribute.
     */
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

    /**
     * Scope a query to only include business customers.
     */
    #[Scope]
    protected function business(Builder $query): Builder
    {
        return $query->where('type', CustomerType::BUSINESS);
    }

    /**
     * Scope a query to only include private customers.
     */
    #[Scope]
    protected function private(Builder $query): Builder
    {
        return $query->where('type', CustomerType::PRIVATE);
    }

    /**
     * Scope a query to filter by status.
     *
     * @param  Builder<Customer>  $query
     * @return Builder<Customer>
     */
    #[Scope]
    protected function withStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
