<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('term_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('term'); // English Term or query string
            $table->string('suggested_term'); // User's Arabic replacement
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('votes')->default(0);
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('term');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_suggestions');
    }
};
