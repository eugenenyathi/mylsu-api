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
        Schema::create('residence', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->enum('student_type', ['Con', 'Block']);
            $table->enum('hostel', ['Suburb', 'Mbundani']);
            $table->integer('room');
            $table->enum('part', [1.1, 1.2, 2.1, 2.2, 3.1, 3.2, 4.1, 4.2]);
            $table->datetime('checkedIn')->nullable();
            $table->datetime('checkedOut')->nullable();
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
        Schema::dropIfExists('residence');
    }
};
