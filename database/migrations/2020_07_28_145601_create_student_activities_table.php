<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->boolean('featured')->default(0);
            $table->unsignedBigInteger('institute_id')->nullable();
            $table->boolean('is_institute')->default(0);
            $table->date('date')->nullable();;
            $table->timestamps();
            $table->Index(['user_id','type','featured']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_activities');
    }
}
