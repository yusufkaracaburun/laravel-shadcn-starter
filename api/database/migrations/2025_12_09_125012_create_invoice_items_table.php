<?php

declare(strict_types=1);

use App\Models\Invoice;
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
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Invoice::class)->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 5)->default(1);
            $table->money('unit_price')->default(0);
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->money('total_excl_vat')->default(0);
            $table->money('total_vat')->default(0);
            $table->money('total_incl_vat')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invoice_id']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
