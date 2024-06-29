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
        Schema::create('examiners_invitations', function (Blueprint $table) {
            $table->id();


            $table->foreignId("invitation_id")->constrained();
            $table->foreignId("examiner_id")->constrained();

            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examiners_invitations');
    }
};