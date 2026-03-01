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
        // Add status and rejection_reason to terms table
        Schema::table('terms', function (Blueprint $table) {
            if (!Schema::hasColumn('terms', 'status')) {
                $table->enum('status', ['unverified', 'accepted', 'rejected'])->default('unverified');
            }
            if (!Schema::hasColumn('terms', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });

        // Add verification_status and rejection_reason to resources table
        Schema::table('resources', function (Blueprint $table) {
            if (!Schema::hasColumn('resources', 'verification_status')) {
                $table->enum('verification_status', ['unverified', 'accepted', 'rejected'])->default('unverified');
            }
            if (!Schema::hasColumn('resources', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            if (Schema::hasColumn('terms', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('terms', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        Schema::table('resources', function (Blueprint $table) {
            if (Schema::hasColumn('resources', 'verification_status')) {
                $table->dropColumn('verification_status');
            }
            if (Schema::hasColumn('resources', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
