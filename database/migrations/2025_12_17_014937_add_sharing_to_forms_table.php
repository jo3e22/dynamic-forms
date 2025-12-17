<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->enum('sharing_type', [
                'authenticated_only',  // Only logged-in users
                'guest_allowed',       // Guests can submit without login
                'guest_email_required' // Guests required to provide email
            ])->default('authenticated_only')->after('status');
            
            $table->boolean('allow_duplicate_responses')->default(true)->after('sharing_type');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['sharing_type', 'allow_duplicate_responses']);
        });
    }
};