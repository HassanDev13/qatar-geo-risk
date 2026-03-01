<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_suggestion_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_suggestion_id')->constrained('term_suggestions')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint: user can vote only once per suggestion
            $table->unique(['user_id', 'term_suggestion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_suggestion_votes');
    }
};
