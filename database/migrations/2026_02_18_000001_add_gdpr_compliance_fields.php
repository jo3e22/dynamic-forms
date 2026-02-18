<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add GDPR compliance fields to support data retention policies and audit logging
     */
    public function up(): void
    {
        // Add GDPR fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('consent_gdpr_at')->nullable()->comment('GDPR data processing consent timestamp');
            $table->timestamp('consent_marketing_at')->nullable()->comment('Marketing consent timestamp');
            $table->timestamp('requested_deletion_at')->nullable()->comment('When user requested account deletion (soft delete period)');
            $table->json('gdpr_preferences')->nullable()->comment('User privacy preferences (data retention, analytics opt-out, etc)');
        });

        // Add GDPR fields to submissions table
        Schema::table('submissions', function (Blueprint $table) {
            $table->timestamp('retention_until')->nullable()->comment('When this submission should be permanently deleted per GDPR');
            $table->boolean('contains_pii')->default(false)->comment('Whether submission contains personally identifiable information');
        });

        // Create data retention policies table
        Schema::create('gdpr_data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('e.g., "Default", "Archived Forms"');
            $table->unsignedInteger('retention_days')->comment('How many days to retain data (0 = indefinite)');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamp('applies_from')->comment('When this policy takes effect');
            $table->timestamps();
        });

        // Create GDPR audit log table (separate from general activity logs for legal compliance)
        Schema::create('gdpr_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action')->comment('data_accessed, data_exported, data_deleted, consent_given, consent_withdrawn, etc');
            $table->string('entity_type')->comment('User, Submission, Form, etc');
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Who performed the action');
            $table->string('actor_type')->comment('admin, user, system, automated_deletion');
            $table->text('reason')->nullable()->comment('Why the action was performed');
            $table->json('data_summary')->nullable()->comment('Summary of what was accessed/deleted without sensitive details');
            $table->string('status')->default('completed')->comment('completed, pending, failed');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });

        // Data Subject Access Request (DSAR) tracker
        Schema::create('gdpr_data_subject_access_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->enum('request_type', ['access', 'export', 'deletion', 'rectification'])->comment('Type of DSAR');
            $table->text('reason')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('deadline_at')->comment('GDPR requires 30-day response');
            $table->timestamp('completed_at')->nullable();
            $table->json('response_data')->nullable()->comment('Exported data for access requests');
            $table->string('download_token')->nullable()->comment('Secure token for downloading exported data');
            $table->timestamp('token_expires_at')->nullable()->comment('Token validity (e.g., 7 days)');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'deadline_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Consent records for future regulations (ePrivacy, etc)
        Schema::create('gdpr_consent_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('consent_type')->comment('gdpr, marketing_email, analytics, cookies');
            $table->boolean('given')->comment('true = consent given, false = withdrawn');
            $table->string('version')->comment('Consent form version for audit trail');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'consent_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_consent_logs');
        Schema::dropIfExists('gdpr_data_subject_access_requests');
        Schema::dropIfExists('gdpr_audit_logs');
        Schema::dropIfExists('gdpr_data_retention_policies');
        
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['retention_until', 'contains_pii']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['consent_gdpr_at', 'consent_marketing_at', 'requested_deletion_at', 'gdpr_preferences']);
        });
    }
};
