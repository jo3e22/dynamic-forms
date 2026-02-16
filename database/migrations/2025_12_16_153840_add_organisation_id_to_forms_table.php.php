<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('template_id')->nullable()->after('organisation_id');
            
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('set null');
            
            $table->index(['organisation_id', 'status']);
            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropForeign(['template_id']);
            $table->dropIndex(['organisation_id', 'status']);
            $table->dropIndex(['template_id']);
            $table->dropColumn(['organisation_id', 'template_id']);
        });
    }
};