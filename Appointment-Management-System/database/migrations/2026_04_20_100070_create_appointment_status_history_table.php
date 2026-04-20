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
        Schema::create('appointment_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('appointment_id')->index();
            $table->enum('old_status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->nullable();
            $table->enum('new_status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show']);
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('appointment_id')->references('id')->on('appointments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_status_history');
    }
};
