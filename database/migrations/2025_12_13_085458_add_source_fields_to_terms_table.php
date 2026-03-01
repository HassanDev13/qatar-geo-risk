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
            $table->string("source_url")->nullable()->after("corrections");
            $table->string("source_type")->nullable()->after("source_url");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("terms", function (Blueprint $table) {
            $table->dropColumn(["source_url", "source_type"]);
        });
    }
};
