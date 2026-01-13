<?php

declare(strict_types=1);

use App\Enums\CustomerType;
use App\Enums\CustomerStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->enum('type', CustomerType::values())->default(CustomerType::PRIVATE->value);
            $table->string('name');

            // Business fields (only required if type = business)
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('phone')->nullable();
            $table->string('kvk_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('iban_number', 34)->nullable();

            $table->enum('status', CustomerStatus::values())->default(CustomerStatus::REGISTERED->value);
            $table->timestamps();

            $table->index(['type']);
            $table->index(['name']);
            $table->index(['email']);
            $table->index(['name', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
