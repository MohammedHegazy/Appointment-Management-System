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
        Schema::create('file_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('entity_type', 50);
            $table->unsignedInteger('entity_id');
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->integer('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('uploaded_by')->index();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_attachments');
    }
};
