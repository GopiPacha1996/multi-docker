<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('courses')->default(0);
            $table->unsignedBigInteger('mock_packages')->default(0);
            $table->unsignedBigInteger('ebook_packages')->default(0);
            $table->unsignedBigInteger('users')->default(0);
            $table->unsignedBigInteger('institutes')->default(0);
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
        Schema::dropIfExists('partner_data');
    }
}
