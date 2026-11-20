<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content-management schema: pages and their sections (draft/published JSON),
 * the media library with usage tracking, packages with per-package itinerary
 * days and gallery, destinations, redirects, announcements and popups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->index();            // image | video | document
            $table->string('disk', 32)->default('public');
            $table->string('path');                          // original (or largest) file
            $table->string('original_name')->nullable();
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds, videos (admin-entered)
            $table->json('variants')->nullable();            // {"480": "media/…-480.webp", …}
            $table->string('title')->nullable();             // library label
            $table->text('alt')->nullable();                 // default alt text
            $table->text('caption')->nullable();
            $table->string('credit_title')->nullable();
            $table->string('credit_author')->nullable();
            $table->string('credit_license', 100)->nullable();
            $table->string('credit_license_url', 500)->nullable();
            $table->string('credit_source_url', 500)->nullable();
            $table->text('credit_changes')->nullable();
            $table->string('legacy_key')->nullable()->unique(); // import key, e.g. "image:hero"
            $table->json('legacy_paths')->nullable();           // old public URLs that redirect here
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->morphs('owner');
            $table->string('field');   // e.g. "published.hero.image" or "gallery"
            $table->string('label');   // human readable location
            $table->timestamps();
            $table->index(['owner_type', 'owner_id', 'field']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('title');
            $table->string('path')->nullable();      // public URL, null for templates/global
            $table->string('kind', 16)->default('page'); // page | template | global
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('type', 64);
            $table->string('label');
            $table->unsignedInteger('sort')->default(0);
            $table->json('draft_data')->nullable();
            $table->json('published_data')->nullable();
            $table->timestamp('draft_updated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['page_id', 'key']);
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('overview');
            $table->unsignedSmallInteger('nights');
            $table->unsignedSmallInteger('days');
            $table->foreignId('image_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->foreignId('mobile_image_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('image_alt')->nullable();
            $table->string('focal', 16)->default('50% 50%');
            $table->json('stays')->nullable();       // [{place, nights}]
            $table->json('tags')->nullable();        // ["Havelock", …]
            $table->json('inclusions')->nullable();  // ["…"]
            $table->json('exclusions')->nullable();
            $table->json('notes')->nullable();       // package-specific travel notes
            $table->string('price_mode', 16)->default('on_request'); // on_request | from | fixed
            $table->char('currency', 3)->default('INR');
            $table->decimal('price_amount', 12, 2)->nullable();
            $table->decimal('price_original', 12, 2)->nullable();
            $table->string('price_unit', 32)->default('per_person');
            $table->date('price_valid_from')->nullable();
            $table->date('price_valid_until')->nullable();
            $table->string('price_note', 300)->nullable();
            $table->foreignId('itinerary_pdf_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('featured_order')->default(0);
            $table->unsignedInteger('sort')->default(0);
            $table->string('status', 16)->default('draft')->index(); // draft | published | archived
            $table->json('published_snapshot')->nullable();
            $table->string('published_slug')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('has_unpublished_changes')->default(true);
            $table->string('import_key')->nullable()->unique();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('package_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->string('title');
            $table->text('description'); // paragraphs separated by blank lines
            $table->timestamps();
            $table->index(['package_id', 'sort']);
        });

        Schema::create('package_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->string('caption')->nullable();
            $table->string('alt')->nullable();
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('official_name');
            $table->foreignId('image_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->foreignId('mobile_image_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('image_alt')->nullable();
            $table->string('focal', 16)->default('50% 50%');
            $table->foreignId('secondary_image_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('secondary_alt')->nullable();
            $table->boolean('secondary_portrait')->default(false);
            $table->string('short');
            $table->string('intro');
            $table->text('body');
            $table->json('highlights')->nullable();
            $table->text('note')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->json('reels')->nullable(); // [{video_id, poster_id, title, text}]
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->string('status', 16)->default('draft')->index();
            $table->json('published_snapshot')->nullable();
            $table->string('published_slug')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('has_unpublished_changes')->default(true);
            $table->string('import_key')->nullable()->unique();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('text', 300);
            $table->string('link_url', 500)->nullable();
            $table->string('link_label', 80)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('targeting', 16)->default('all'); // all | selected
            $table->json('page_keys')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // internal campaign name
            $table->foreignId('image_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('image_alt')->nullable();
            $table->string('heading')->nullable();
            $table->text('description')->nullable();
            $table->string('button_label', 80)->nullable();
            $table->string('button_url', 500)->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->string('targeting', 16)->default('all');
            $table->json('page_keys')->nullable();
            $table->unsignedSmallInteger('delay_seconds')->default(5);
            $table->string('frequency', 16)->default('session'); // session | day | week | once
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('priority')->default(10);
            $table->string('campaign_version', 16);       // changes whenever content changes
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'popups', 'announcements', 'redirects', 'destinations', 'package_media',
            'package_days', 'packages', 'sections', 'pages', 'media_usages', 'media'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
