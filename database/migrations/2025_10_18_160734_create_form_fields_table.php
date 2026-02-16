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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->string('label');               // Question label
            $table->string('type');                // e.g. text, select, checkbox
            $table->json('options')->nullable();   // For select/checkbox types
            $table->boolean('required')->default(false);
            $table->unsignedInteger('field_order')->default(0); // Field order in the form
            $table->unsignedInteger('section')->default(0); // Section number in the form
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('form_fields', function (Blueprint $table) {
            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
