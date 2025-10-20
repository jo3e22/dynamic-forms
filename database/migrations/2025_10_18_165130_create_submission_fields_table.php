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
        Schema::create('submission_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_field_id');
            $table->unsignedBigInteger('form_submission_id');
            $table->json('answer')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('submission_fields', function (Blueprint $table) {
            $table->foreign('form_field_id')->references('id')->on('form_fields')->onDelete('cascade');
            $table->foreign('form_submission_id')->references('id')->on('form_submissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_fields');
    }
};
