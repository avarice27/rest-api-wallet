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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid()->primary();
            // $table->foreignUuid('user_id')->constrained('users', 'uuid');
            // $table->foreignUuid('order_status_id')->constrained('order_status', 'uuid');
            $table->foreignId('payment_method_id')->constrained('payment_methods', 'id');
            $table->string('transaction_code')->nullable();
            $table->float('total_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
