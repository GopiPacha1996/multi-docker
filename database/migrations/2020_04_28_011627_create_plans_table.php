<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plan_id');
            $table->string('plan_desc')->nullable();
            $table->enum('type', ['course', 'app', 'category', 'creator'])->default('app');
            $table->unsignedBigInteger('type_id')->nullable();
            $table->boolean('active')->default(true);
            $table->float('amount');
            $table->integer('trail')->default(0);
            $table->integer('interval');
            $table->string('period');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'type_id', 'plan_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
