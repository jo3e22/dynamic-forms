<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            // API integration fields
            $table->json('schema')->nullable()->after('secondary_color');
            $table->string('api_key')->unique()->nullable()->after('schema');
            $table->json('api_config')->nullable()->after('api_key');
            $table->string('source')->default('web')->after('api_config'); // 'web', 'api'
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->string('source')->default('web')->after('id'); // 'web', 'api', 'embedded'
            $table->string('api_client')->nullable()->after('source'); // Which org/app submitted
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['schema', 'api_key', 'api_config', 'source']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['source', 'api_client']);
        });
    }
};
