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
        Schema::create("term_feedback", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("term_id")
                ->constrained("terms")
                ->onDelete("cascade");
            $table->boolean("is_positive")->default(true);
            $table
                ->string("user_ip_hash", 64)
                ->nullable()
                ->comment("SHA256 hash of IP + app key for anonymity");
            $table
                ->string("user_agent_hash", 64)
                ->nullable()
                ->comment("SHA256 hash of user agent + app key for anonymity");
            $table->timestamps();

            // Index for faster queries
            $table->index(["term_id", "is_positive"]);
            $table->index(["user_ip_hash", "user_agent_hash"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("term_feedback");
    }
};
