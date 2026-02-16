<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('form_settings', function (Blueprint $table) {
            $table->enum('publish_mode', ['manual', 'scheduled'])
                ->default('manual')
                ->after('form_id');
            $table->boolean('is_published')
                ->default(false)
                ->after('publish_mode');
        });

        // Remove status from forms table — it's now computed
        // (Keep column for now but stop relying on it)
    }

    public function down(): void
    {
        Schema::table('form_settings', function (Blueprint $table) {
            $table->dropColumn(['publish_mode', 'is_published']);
        });
    }
};