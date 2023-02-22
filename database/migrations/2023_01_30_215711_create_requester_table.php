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
        Schema::create('requester', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->enum('student_type', ['Con', 'Block']);
            $table->enum('gender', ['Female', 'Male']);
            $table->enum('processed', ['Yes', 'No'])->default('No');
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requester');
    }
};
