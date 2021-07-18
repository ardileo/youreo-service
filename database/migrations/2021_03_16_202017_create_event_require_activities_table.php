<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventRequireActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_require_activities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_require');
            $table->foreign('id_require')
                ->references('id')
                ->on('event_require')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('activity_detail');

            $table->unsignedBigInteger('activity_added_by');
            $table->foreign('activity_added_by')
                ->references('id_user')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->enum('status', ['delete', 'publish']);

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
        Schema::dropIfExists('event_require_activities');
    }
}
