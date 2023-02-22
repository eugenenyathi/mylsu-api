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
        Schema::create('request_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('requester_id');
            $table->string('selected_roomie');
            $table->enum('student_type', ['Con', 'Block']);
            $table->enum('gender', ['Female', 'Male']);
            $table->enum('selection_confirmed', ['Yes', 'No', 'Waiting'])->default('Waiting');
            $table->foreign('requester_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('selected_roomie')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_candidates');
    }
};
