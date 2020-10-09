<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlanIdToInstitutePayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('institute_payment', function (Blueprint $table) {
            $table->bigInteger('new_plan_id')->nullable();
            $table->bigInteger('address_id')->nullable();
            $table->bigInteger('subscription_id')->nullable();
            $table->string('tax', 20)->nullable();
            $table->bigInteger('old_plan_id')->nullable();
            $table->string('action', 20)->nullable();
            $table->string('plan_amount')->nullable();
            $table->string('applicable_amount')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('institute_payment', function (Blueprint $table) {
            $table->dropColumn('new_plan_id');
            $table->dropColumn('address_id');
            $table->dropColumn('tax');
            $table->dropColumn('subscription_id');
            $table->dropColumn('old_plan_id');
            $table->dropColumn('plan_amount');
            $table->dropColumn('applicable_amount');
            $table->dropColumn('action');
        });
    }
}
