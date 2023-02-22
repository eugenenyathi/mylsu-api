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
        Schema::create('profile', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('program_id');
            $table->enum('part', [1.1, 1.2, 2.1, 2.2, 3.1, 3.2, 4.1, 4.2]);
            $table->enum('student_type', ['Conventional', 'Block']);
            $table->year('enrolled');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('programmes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profile');
    }
};
