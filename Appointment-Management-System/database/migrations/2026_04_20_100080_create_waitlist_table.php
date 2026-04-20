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
        Schema::create('waitlist', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_id')->index();
            $table->unsignedInteger('doctor_id')->index();
            $table->date('preferred_date');
            $table->time('preferred_time')->nullable();
            $table->enum('status', ['waiting', 'offered', 'accepted', 'expired'])->default('waiting');
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('doctor_id')->references('id')->on('doctors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlist');
    }
};
