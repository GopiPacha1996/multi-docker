<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string("deviceToken");
            $table->string("deviceUUID")->unique();

            $table->string("deviceOS")->nullable();
            $table->string("deviceMake")->nullable();
            $table->string("deviceOSVersion")->nullable();
            $table->string("deviceModel")->nullable();
            $table->string("deviceUsername")->nullable();
            $table->string("platform")->nullable();

            $table->boolean('active')->default(true);

            $table->index(['deviceUUID', 'active', 'created_at']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
