<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. publications
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['KDA', 'DDA', 'SKD']);
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->integer('year');
            $table->string('title');
            $table->string('catalog_number')->nullable();
            $table->string('publication_number')->nullable();
            $table->string('issn')->nullable();
            $table->string('volume')->nullable();
            $table->string('book_size')->default('A5');
            $table->enum('status', [
                'PENDING_DATA',
                'DATA_INGESTED',
                'IN_EDITORIAL',
                'PENDING_APPROVAL',
                'APPROVED_LOCKED',
                'FINAL_RELEASED'
            ])->default('PENDING_DATA');
            $table->dateTime('soft_deadline')->nullable();
            $table->dateTime('hard_deadline')->nullable();
            $table->timestamps();
        });

        // 2. raw_data_files
        Schema::create('raw_data_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->onDelete('cascade');
            $table->string('opd_source_name');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('file_hash_sha256', 64);
            $table->integer('version_number')->default(1);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->string('status')->default('PROCESSED');
            $table->timestamps();
        });

        // 3. mapping_schemas
        Schema::create('mapping_schemas', function (Blueprint $table) {
            $table->id();
            $table->string('opd_source_name');
            $table->string('table_identifier');
            $table->string('data_mode')->default('DIRECT'); // AGGREGATE or DIRECT
            $table->json('mapping_rules');
            $table->timestamps();
        });

        // 4. publication_tables
        Schema::create('publication_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->onDelete('cascade');
            $table->integer('chapter_number');
            $table->string('table_number');
            $table->string('title_id');
            $table->string('title_en')->nullable();
            $table->string('source_agency')->nullable();
            $table->json('table_data');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // 5. chapter_narratives
        Schema::create('chapter_narratives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->onDelete('cascade');
            $table->integer('chapter_number');
            $table->string('title_id');
            $table->string('title_en')->nullable();
            $table->longText('narrative_id')->nullable();
            $table->longText('narrative_en')->nullable();
            $table->string('highlight_label')->nullable();
            $table->string('highlight_value')->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. visual_assets
        Schema::create('visual_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->onDelete('cascade');
            $table->string('asset_type'); // POPULATION_PYRAMID, CLIMATE_CHART, SKD_CARTESIAN, COVER_CUSTOM, MAP
            $table->integer('chapter_number')->nullable();
            $table->enum('mode', ['AUTO_GENERATED', 'MANUAL_OVERRIDE'])->default('AUTO_GENERATED');
            $table->string('file_path');
            $table->timestamps();
        });

        // 7. workflow_logs
        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->onDelete('cascade');
            $table->string('from_status');
            $table->string('to_status');
            $table->foreignId('user_id')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
        Schema::dropIfExists('visual_assets');
        Schema::dropIfExists('chapter_narratives');
        Schema::dropIfExists('publication_tables');
        Schema::dropIfExists('mapping_schemas');
        Schema::dropIfExists('raw_data_files');
        Schema::dropIfExists('publications');
    }
};
