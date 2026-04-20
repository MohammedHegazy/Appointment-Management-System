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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('appointment_id')->index();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prescription')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('medical_records');
    }
};
