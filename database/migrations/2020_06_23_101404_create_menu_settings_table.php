<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_type');
            $table->string('title');
            $table->string('routes')->nullable();
            $table->string('icons')->nullable();
            $table->bigInteger('sort_order')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->text('permission')->nullable();
            $table->boolean('is_parent')->default(false)->nullable();
            $table->string('type', 20);
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->boolean('is_locked')->default(false)->nullable();
            $table->boolean('is_new')->default(false)->nullable();
            $table->string('status', 20);
            $table->index(['title', 'type', 'user_type', 'plan_id', 'status']);
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
        Schema::dropIfExists('menu_settings');
    }
}
