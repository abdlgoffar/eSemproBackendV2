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
        Schema::create('examiners_proposals', function (Blueprint $table) {
            $table->id();

            $table->foreignId("proposal_id")->constrained();
            $table->foreignId("examiner_id")->constrained();
            $table->enum('examiner_assessment_status', ['pending', 'accepted', 'rejected', 'revision'])->default('pending');
            $table->string("suggestion", 900)->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examiners_proposals');
    }
};