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
        Schema::create('supevisors_proposals', function (Blueprint $table) {
            $table->id();


            $table->foreignId("proposal_id")->constrained();
            $table->foreignId("supervisor_id")->constrained();
            $table->enum('supervisor_approval_status', ['pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->string("suggestion", 900)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supevisors_proposals');
    }
};