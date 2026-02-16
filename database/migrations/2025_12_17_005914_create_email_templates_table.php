<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'submission.received', 'submission.manager_alert'
            $table->string('event'); // Type: submission_received, submission_manager_alert, etc.
            $table->string('name'); // Human-readable name
            $table->string('subject');
            $table->longText('body'); // HTML/Markdown content
            $table->enum('type', ['markdown', 'html'])->default('html');
            $table->foreignId('organisation_id')->nullable()->constrained()->cascadeOnDelete(); // Org-specific override
            $table->boolean('enabled')->default(true);
            $table->string('locale')->default('en');
            $table->json('variables')->nullable(); // Available variables: {form_title}, {organisation_name}, {recipient_name}, etc.
            $table->boolean('is_default')->default(false); // System default template
            $table->timestamps();
            $table->index(['event', 'organisation_id', 'locale']);
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_email');
            $table->string('event');
            $table->foreignId('form_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->longText('body');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['event', 'form_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
    }
};