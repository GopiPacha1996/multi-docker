<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubAdminHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_admin_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_admin_id');
            $table->string('type')->index('type')->nullable();
            $table->string('type_id')->index('type_id')->nullable();
            $table->string('menu');
            $table->string('action');
            $table->longText('comment')->nullable();
            $table->foreign('sub_admin_id')->references('id')->on('sub_admins')
                ->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('sub_admin_histories');
    }
}
