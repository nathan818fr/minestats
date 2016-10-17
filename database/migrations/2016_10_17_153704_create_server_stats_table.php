<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('server_id');
            $table->dateTime('recorded_at');
            $table->mediumInteger('players');

            $table->foreign('server_id')
                ->references('id')->on('servers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_stats');
    }
}
