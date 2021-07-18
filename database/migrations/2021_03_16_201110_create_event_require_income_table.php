<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventRequireIncomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_require_income', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_require');
            $table->foreign('id_require')
                ->references('id')
                ->on('event_require')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('income_name', 128);
            $table->string('income_detail', 128);
            $table->string('income_nominal', 128);

            $table->unsignedBigInteger('income_added_by');
            $table->foreign('income_added_by')
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
        Schema::dropIfExists('event_require_income');
    }
}
