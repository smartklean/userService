<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
          $table->string('first_name')->nullable()->change();
          $table->string('last_name')->nullable()->change();
          $table->string('phone_number')->nullable()->change();
          $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
          $table->string('first_name')->change();
          $table->string('last_name')->change();
          $table->string('phone_number')->unique()->change();
          $table->string('password')->change();
        });
    }
}
