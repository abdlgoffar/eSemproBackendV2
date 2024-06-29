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
        Schema::create('proposal_pdfs', function (Blueprint $table) {
            $table->id();
            $table->string("saved_name", 600)->nullable();
            $table->string("original_name", 600)->nullable();
            $table->string("path", 600)->nullable();
      
            $table->foreignId("proposal_id");

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_pdfs');
    }
};