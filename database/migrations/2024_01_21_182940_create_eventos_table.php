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
        Schema::create('eventos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('tipo')->nullable();
            $table->longText('datos')->nullable();
            $table->text('mensaje')->nullable();
            $table->string('bot')->nullable();
            $table->string('desde')->nullable();
            $table->string('file')->nullable();
            $table->string('extension')->nullable();
            $table->string('subtipo')->nullable();
            $table->string('author')->nullable();
            $table->string('subtype')->nullable();
            $table->string('clase')->nullable();
            $table->string('whatsapp')->nullable();
            $table->integer('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventos');
    }
};
