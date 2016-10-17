<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_languages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('server_id');
            $table->char('language', 2);
            $table->boolean('main');

            $table->foreign('server_id')
                ->references('id')->on('servers')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('language')
                ->references('id')->on('languages')
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
        Schema::dropIfExists('server_languages');
    }
}
