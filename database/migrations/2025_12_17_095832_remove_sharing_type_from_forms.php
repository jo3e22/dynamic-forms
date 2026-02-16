<?php
// database/migrations/xxxx_xx_xx_xxxxxx_remove_sharing_type_from_forms.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['sharing_type', 'allow_duplicate_responses']);
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->enum('sharing_type', [
                'authenticated_only',
                'guest_allowed',
                'guest_email_required'
            ])->default('authenticated_only')->after('status');
            $table->boolean('allow_duplicate_responses')->default(true)->after('sharing_type');
        });
    }
};