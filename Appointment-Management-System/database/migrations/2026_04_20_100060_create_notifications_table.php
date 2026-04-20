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
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->enum('type', [
                'appointment_reminder',
                'appointment_confirmed',
                'appointment_cancelled',
                'payment_receipt',
                'payment_failed',
                'account_activated',
                'general',
            ]);
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->unsignedInteger('related_id')->nullable();
            $table->string('related_type', 50)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
