<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Customer;
use App\Enums\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();

            // Foreign keys
            $table->foreignIdFor(Invoice::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->nullOnDelete();

            // Payment details
            $table->string('payment_number')->unique();
            $table->money('amount')->default(0);

            // Payment method and reference
            $table->string('method')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->enum('status', PaymentStatus::values())->default(PaymentStatus::PENDING->value);

            // Payment date fields
            $table->timestamp('date')->default(now());
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['invoice_id']);
            $table->index(['customer_id']);
            $table->index(['method']);
            $table->index(['provider']);
            $table->index(['status']);
            $table->index(['date']);
            $table->index(['paid_at']);

            // Composite indexes for common queries
            $table->index(['invoice_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['method', 'status']);
            $table->index(['provider', 'status']);
            $table->index(['date', 'status']);
            $table->index(['paid_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
