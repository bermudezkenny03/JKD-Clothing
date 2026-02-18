<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
          $table->id();
          $table->foreignId('user_id')->constrained()->cascadeOnDelete();
          $table->string('order_number')->unique();
          $table->decimal('subtotal', 12, 2);
          $table->decimal('tax', 12, 2)->default(0);
          $table->decimal('discount', 12, 2)->default(0);
          $table->decimal('shipping_cost', 12, 2)->default(0);
          $table->decimal('total', 12, 2);

          $table->foreignId('order_status_id')
          ->constrained('parameters');
          
          $table->foreignId('shipping_status_id')
          ->nullable()
          ->constrained('parameters');

          $table->json('billing_address');
          $table->json('shipping_address');

          $table->timestamp('placed_at')->nullable();
          $table->timestamps();
          $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
