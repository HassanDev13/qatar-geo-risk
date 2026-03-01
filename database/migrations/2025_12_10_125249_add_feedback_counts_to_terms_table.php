<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("terms", function (Blueprint $table) {
            $table
                ->integer("positive_feedback_count")
                ->default(0)
                ->after("corrections")
                ->comment("Number of positive feedback votes");
            $table
                ->integer("negative_feedback_count")
                ->default(0)
                ->after("positive_feedback_count")
                ->comment("Number of negative feedback votes");
            $table
                ->integer("total_feedback_count")
                ->default(0)
                ->after("negative_feedback_count")
                ->comment("Total number of feedback votes");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("terms", function (Blueprint $table) {
            $table->dropColumn([
                "positive_feedback_count",
                "negative_feedback_count",
                "total_feedback_count",
            ]);
        });
    }
};
