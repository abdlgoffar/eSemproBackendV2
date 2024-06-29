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
        Schema::create('supervisors', function (Blueprint $table) {
            // column
            $table->id();
            $table->string("name", 100)->nullable(false);
            $table->string("address", 300)->nullable(false);
            $table->string("phone", 20)->nullable(false)->unique("users_phone_unique");

            // fk
            $table->foreignId("user_id")->nullable(false);


            // config
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisors');
    }
};