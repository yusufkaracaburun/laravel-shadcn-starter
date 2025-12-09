<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Enums\InvoiceStatus;
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
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique()->nullable();
            $table->date('date');
            $table->integer('due_days')->default(30);
            $table->date('date_due')->default(now()->addDays(30));
            $table->enum('status', InvoiceStatus::values())->default(InvoiceStatus::DRAFT->value);

            $table->money('subtotal')->default(0);
            $table->money('total_vat_0')->default(0);
            $table->money('total_vat_9')->default(0);
            $table->money('total_vat_21')->default(0);
            $table->money('total')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['customer_id']);
            $table->index(['status']);
            $table->index(['date']);
            $table->index(['date_due']);
            $table->index(['invoice_number']);

            // Composite indexes for common queries
            $table->index(['customer_id', 'status']);
            $table->index(['status', 'date']);
            $table->index(['customer_id', 'date']);
            $table->index(['customer_id', 'date_due']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
