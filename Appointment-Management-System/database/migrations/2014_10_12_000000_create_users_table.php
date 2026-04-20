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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('avatar', 500)->nullable();
            $table->unsignedInteger('hospital_id')->nullable()->index();
            $table->enum('role', ['admin', 'doctor', 'hospital_admin', 'patient']);
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->foreign('hospital_id')->references('id')->on('hospitals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
