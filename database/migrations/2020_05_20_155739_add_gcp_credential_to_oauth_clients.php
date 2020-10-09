<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGcpCredentialToOauthClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->json('gcp_credential')->nullable();
            $table->string('credential_url')->nullable();
            $table->string('gcp_account')->nullable();
            $table->string('gcp_project')->nullable();
            $table->string('package_id')->nullable();
            $table->index(['gcp_account', 'gcp_project', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn('gcp_credential');
            $table->dropColumn('credential_url');
            $table->dropColumn('gcp_account');
            $table->dropColumn('gcp_project');
            $table->dropColumn('package_id');
        });
    }
}
