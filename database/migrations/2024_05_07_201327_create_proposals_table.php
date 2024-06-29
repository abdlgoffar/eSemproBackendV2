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
        Schema::create('proposals', function (Blueprint $table) {
           
          
            $table->id();
        
            $table->string("title", 700)->nullable(false);
            $table->string("period", 100)->nullable(false);
            $table->date('upload_date')->nullable(false);
            
            $table->enum('head_study_program_approval_status', ['pending', 'approved'])->default('pending');
            $table->enum('supervisors_approval_status', ['pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->enum('coordinator_approval_status', ['pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->enum('examiners_approval_status', ['pending', 'approved', 'rejected', 'revision'])->default('pending');

            $table->foreignId("room_id")->nullable();

            $table->softDeletes();
            $table->timestamps();

         
            $table->foreignId("student_id")->nullable(false);
          
            $table->foreignId("head_study_program_id")->nullable();
            $table->foreignId("invitation_id")->nullable();
           
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};