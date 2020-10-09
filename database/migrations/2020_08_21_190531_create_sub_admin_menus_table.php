<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubAdminMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_admin_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sub_admin_id');
            $table->unsignedBigInteger('menu_setting_id');

            $table->boolean('active')->default(true)->index('active');

            $table->foreign('sub_admin_id')->references('id')->on('sub_admins')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('menu_setting_id')->references('id')->on('menu_settings')
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
        Schema::dropIfExists('sub_admin_menus');
    }
}
