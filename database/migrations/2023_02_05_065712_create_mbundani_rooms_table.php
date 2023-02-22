<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mbundani_rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('room');
            $table->enum('usable', ['Yes', 'No'])->default('Yes');
            $table->enum('con_occupied', ['Yes', 'No'])->default('No');
            $table->enum('block_occupied', ['Yes', 'No'])->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mbundani_rooms');
    }
};
