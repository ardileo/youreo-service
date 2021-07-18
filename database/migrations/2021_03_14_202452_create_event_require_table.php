<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventRequireTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_require', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_event');
            $table->foreign('id_event')
                ->references('id')
                ->on('events')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('req_name', 80);
            $table->string('req_desc')->nullable();
            $table->integer('req_budget')->unsigned()->nullable();
            $table->json('req_contribs')->nullable();
            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->enum('finished', ['yes', 'no']);

            $table->enum('status', ['on', 'off']);

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
        Schema::dropIfExists('event_require');
    }
}
