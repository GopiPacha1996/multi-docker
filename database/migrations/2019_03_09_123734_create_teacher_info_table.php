<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('type')->comment('instructor type');
            $table->boolean('pathshala_employee');
            $table->string('demo_video')->nullable();
            $table->longText('about')->nullable();
            $table->string('admin_status')->nullable();
            $table->string('admin_comment')->nullable();
            $table->unsignedBigInteger('done_by')->nullable();
            $table->unsignedBigInteger('plan_log_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->foreign('plan_log_id')->references('id')->on('user_plan_log');
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_info');
    }
}
