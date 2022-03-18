<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLoginByToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login_by');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->enum('login_by',array('manual','facebook','google','apple'));

        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('login_by');
        });
        Schema::table('providers', function (Blueprint $table) {
            $table->enum('login_by',array('manual','facebook','google','apple'));

        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
