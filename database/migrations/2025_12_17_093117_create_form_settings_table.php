<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('form_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade')->unique();
            $table->enum('sharing_type', [
                'authenticated_only',
                'guest_allowed',
                'guest_email_required'
            ])->default('authenticated_only');
            $table->boolean('allow_duplicate_responses')->default(true);
            $table->enum('confirmation_email', [
                'none',
                'confirmation_only',
                'linked_copy_of_responses',
                'detailed_copy_of_responses'
            ])->default('linked_copy_of_responses');
            $table->dateTime('open_at')->nullable();
            $table->dateTime('close_at')->nullable();
            $table->unsignedInteger('max_submissions')->nullable();
            $table->boolean('allow_response_editing')->default(true);
            $table->text('confirmation_message')->nullable();


            // Add more settings as needed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_settings');
    }
};