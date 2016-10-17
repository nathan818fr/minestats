<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->string('ip', 45); // IPv4 or IPv6 textual representation
            $table->unsignedSmallInteger('port')->nullable();
            $table->enum('type', ['PC', 'PE']);
            $table->binary('icon')->nullable();
            $table->mediumInteger('players');
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servers');
    }
}
