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
        Schema::create('contactos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('avatar')->nullable();
            $table->string('number')->nullable();
            $table->tinyInteger('isBusiness')->nullable();
            $table->tinyInteger('isEnterprise')->nullable();
            $table->string('name')->nullable();
            $table->string('shortName')->nullable();
            $table->string('type')->nullable();
            $table->tinyInteger('isMe')->nullable();
            $table->tinyInteger('isUser')->nullable();
            $table->tinyInteger('isGroup')->nullable();
            $table->tinyInteger('isWAContact')->nullable();
            $table->tinyInteger('isMyContact')->nullable();
            $table->tinyInteger('isBlocked')->nullable();
            $table->string('bot')->nullable();
            $table->string('_id')->nullable();
            $table->string('codigo')->nullable();
            $table->tinyInteger('send')->nullable();
            $table->string('class')->nullable();
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
        Schema::dropIfExists('contactos');
    }
};
