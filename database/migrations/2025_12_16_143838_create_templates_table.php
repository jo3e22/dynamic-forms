<?php
// database/migrations/2025_12_16_143838_create_templates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('template_categories')->onDelete('cascade');
            $table->index('parent_id');
            $table->index('sort_order');
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->morphs('owner'); // This ALREADY creates the index
            $table->enum('visibility', ['private', 'organisation', 'public'])->default('private');
            $table->enum('category', ['form', 'section', 'field_group', 'other'])->default('form');
            $table->json('data');
            $table->json('metadata')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('use_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            // DON'T add this - morphs() already creates it!
            // $table->index(['owner_type', 'owner_id']);
            
            $table->index(['visibility', 'category']);
            $table->index('is_featured');
            $table->index('average_rating');
        });

        Schema::create('template_category_template', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('template_category_id');

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->foreign('template_category_id')->references('id')->on('template_categories')->onDelete('cascade');

            $table->unique(['template_id', 'template_category_id'], 'template_category_unique');
        });

        Schema::create('template_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('used_at');

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('template_id');
            $table->index('user_id');
        });

        Schema::create('template_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['template_id', 'user_id']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_ratings');
        Schema::dropIfExists('template_usages');
        Schema::dropIfExists('template_category_template');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('template_categories');
    }
};