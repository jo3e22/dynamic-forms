<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('type', ['school', 'club', 'business', 'non_profit', 'government', 'sports', 'community', 'other'])->default('other');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->boolean('allow_member_form_creation')->default(true);
            $table->boolean('require_form_approval')->default(false);
            $table->boolean('inherit_parent_settings')->default(true);
            $table->boolean('inherit_parent_branding')->default(false);
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('organisations')->onDelete('cascade');
            
            $table->index(['type', 'status']);
            $table->index('owner_id');
            $table->index('parent_id');
        });

        Schema::create('organisation_brandings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organisation_id');
            $table->string('primary_color', 7)->nullable();
            $table->string('secondary_color', 7)->nullable();
            $table->string('accent_color', 7)->nullable();
            $table->string('font_family')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('logo_icon_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->timestamps();

            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->unique('organisation_id');
        });

        Schema::create('organisation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organisation_id');
            $table->text('description')->nullable();
            $table->string('tagline')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            
            // Address fields
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('county', 100)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->string('country', 100)->default('Ireland');
            
            // Social media
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            
            // Additional info
            $table->string('registration_number')->nullable();
            $table->date('established_date')->nullable();
            
            $table->timestamps();

            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->unique('organisation_id');
        });

        // Closure table for efficient hierarchical queries
        Schema::create('organisation_hierarchies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->unsignedInteger('depth'); // 0 = self, 1 = direct child, 2+ = further descendants

            $table->foreign('ancestor_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('descendant_id')->references('id')->on('organisations')->onDelete('cascade');

            $table->unique(['ancestor_id', 'descendant_id']);
            $table->index('depth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_hierarchies');
        Schema::dropIfExists('organisation_details');
        Schema::dropIfExists('organisation_brandings');
        Schema::dropIfExists('organisations');
    }
};