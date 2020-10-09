<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('users', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('phone', 15)->unique()->nullable();
			$table->string('password')->nullable();
			$table->boolean('is_active');
			$table->string('email_otp', 6)->nullable();
			$table->boolean('email_verify')->deafult(0);
			$table->string('phone_otp', 6)->nullable();
			$table->boolean('phone_verify')->deafult(0);
			$table->timestamp('email_verified_at')->nullable();
			$table->rememberToken();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('users');
	}
}
