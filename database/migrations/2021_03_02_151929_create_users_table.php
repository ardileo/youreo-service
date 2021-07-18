<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('username',80)->unique()->nullable();
            $table->string('email', 120)->unique();
            $table->string('phone')->unique();
            $table->string('full_name', 80);
            $table->enum('gender', ['m', 'f', '-']);
            $table->date('born_at')->nullable();;
            $table->string('password');
            $table->string('api_token');
            $table->string('address')->nullable();
            $table->string('city')->nullable();

            $table->json('images')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->json('logs')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
