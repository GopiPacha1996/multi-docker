<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipcodesTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('zipcode', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('country_code', 5);
			$table->string('zipcode', 20);
			$table->string('locality', 20);
			$table->string('state', 20);
			$table->integer('state_code');
			$table->string('district', 20);
			$table->integer('district_code');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('zipcodes');
	}
}
