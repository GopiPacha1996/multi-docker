<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('user_id');

            $table->string('subscription_id');
            $table->string('entity');
            $table->string('customer_id')->nullable();
            $table->string('short_url')->nullable();

            $table->enum('status',
                ['created', 'failed', 'authenticated', 'active', 'pending', 'halted', 'cancelled', 'completed', 'expired'])
                ->default('created');


            $table->timestamp('current_start')->nullable();
            $table->timestamp('current_end')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->integer('quantity')->nullable();
            $table->integer('total_count')->nullable();
            $table->integer('paid_count')->nullable();
            $table->integer('remaining_count')->nullable();

            $table->boolean('customer_notify');

            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->index(['subscription_id', 'customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
