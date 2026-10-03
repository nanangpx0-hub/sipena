<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->longText('preface_id')->nullable()->after('hard_deadline');
            $table->longText('preface_en')->nullable()->after('preface_id');
            $table->string('sign_date', 120)->nullable()->after('preface_en');
            $table->json('custom_abbreviations')->nullable()->after('sign_date');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn(['preface_id', 'preface_en', 'sign_date', 'custom_abbreviations']);
        });
    }
};
