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
        Schema::create('appointment_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('appointment_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'online']);
            $table->string('stripe_payment_id')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->foreign('appointment_id')->references('id')->on('appointments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_payments');
    }
};
