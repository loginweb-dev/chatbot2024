<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapps', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('nombre')->nullable();
            $table->string('codigo')->nullable();
            $table->tinyInteger('estado')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('slug')->nullable();
            $table->string('telefono')->nullable();
            $table->tinyInteger('default')->nullable();
            $table->text('desc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapps');
    }
};
