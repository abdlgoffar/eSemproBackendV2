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
        Schema::create('students', function (Blueprint $table) {
            $table->id();


            // column
            
            $table->string("nrp", 20)->nullable(false)->unique("users_nrp_unique");
            $table->string("name", 100)->nullable(false);
            $table->string("address", 300)->nullable(false);
            $table->string("phone", 20)->nullable(false)->unique("users_phone_unique");

            // config
            $table->softDeletes();
            $table->timestamps();

            // fk
            $table->foreignId("invitation_id")->nullable();
            $table->foreignId("head_study_program_id")->nullable(false);
            $table->foreignId("user_id")->nullable(false);

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};