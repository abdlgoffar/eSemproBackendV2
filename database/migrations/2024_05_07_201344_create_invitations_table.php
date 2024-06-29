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
        Schema::create('invitations', function (Blueprint $table) {
            // column
            $table->id();
        
            
            $table->time('implementation_hour', $precision = 0)->nullable(false);
            $table->date('implementation_date')->nullable(false);

            // config
            $table->softDeletes();
            $table->timestamps();

            // fk
            $table->foreignId("coordinator_id")->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};