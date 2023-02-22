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
        Schema::create('room_ranges', function (Blueprint $table) {
            $table->id();
            $table->integer('first_room')->unique();
            $table->integer('last_room')->unique();
            $table->enum('side', ['F', 'M']);
            $table->enum('floor', ['1st', '2nd', '3rd']);
            $table->enum('suburb_floor_side', ['Left', 'Right']);
            $table->enum('mbundani_floor_side', ['Left', 'Right']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_ranges');
    }
};
