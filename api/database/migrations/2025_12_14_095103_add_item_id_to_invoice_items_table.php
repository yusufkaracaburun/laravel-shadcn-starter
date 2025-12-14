<?php

declare(strict_types=1);

use App\Models\Item;
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
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->foreignIdFor(Item::class)->nullable()->after('invoice_id')->constrained()->nullOnDelete();
            $table->string('unit')->nullable()->after('vat_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->dropForeign(['item_id']);
            $table->dropColumn(['item_id', 'unit']);
        });
    }
};
