<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);

            $table->unsignedBigInteger('category');
            $table->foreign('category')
                ->references('id')
                ->on('event_category')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('guests')->unsigned();
            $table->integer('budgets')->unsigned();
            $table->timestamp('date_start');
            $table->timestamp('date_end');

            $table->unsignedBigInteger('owner');
            $table->foreign('owner')
                ->references('id_user')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->json('members')->nullable();
            $table->json('images')->nullable();

            $table->enum('status', ['publish', 'pending']);

            $table->enum('finished', ['yes', 'no']);

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
        Schema::dropIfExists('events');
    }
}
