<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermsServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms_service', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->longText('details')->nullable();
            $table->string('sub_details')->nullable();
            $table->string('type')->nullable();
            $table->string('sort_order')->nullable();
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
        Schema::dropIfExists('terms_service');
    }
}
