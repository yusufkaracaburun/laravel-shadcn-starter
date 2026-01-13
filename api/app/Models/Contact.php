<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ContactObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ObservedBy([ContactObserver::class])]
final class Contact extends BaseModel
{
    protected $with = ['user'];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'contact_customer')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,
            Customer::class,
            'id', // Foreign key on customers table
            'customer_id', // Foreign key on invoices table
            'id', // Local key on contacts
            'id', // Local key on customers
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => mb_trim("{$this->first_name} {$this->last_name}"),
        );
    }

    #[Scope]
    protected function primary(Builder $query): Builder
    {
        return $query->whereHas('customers', function (Builder $query): void {
            $query->where('is_primary', true);
        });
    }
}
