<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentActivitiesSubtypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_activities_subtype', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sub_type')->nullable();
            $table->unsignedBigInteger('sub_type_id')->nullable();
            $table->unsignedBigInteger('student_activity_id')->nullable();
            $table->timestamps();
            $table->Index(['sub_type','sub_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_activities_subtype');
    }
}
