<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubadminPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subadmin_payment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->string('txnid');
            $table->bigInteger('plan_id')->nullable();
            $table->bigInteger('count_user')->nullable();
            $table->string('plan_amount')->nullable();
            $table->string('pay_amount')->nullable();
            $table->string('tax', 20)->nullable();
            $table->timestamp('plan_start')->nullable();
            $table->timestamp('plan_end')->nullable();
            $table->string('pay_mode');
            $table->string('status', 20);
            $table->string('payment_method', 20);
            $table->bigInteger('old_renew_id')->nullable();
            $table->string('action', 20)->nullable();
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
        Schema::dropIfExists('subadmin_payment');
    }
}
