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
        Schema::create('grupos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('_id')->nullable();
            $table->string('codigo')->nullable();
            $table->string('bot')->nullable();
            $table->string('name')->nullable();
            $table->tinyInteger('isReadOnly')->nullable();
            $table->tinyInteger('isMuted')->nullable();
            $table->longText('groupMetadata')->nullable();
            $table->longText('lastMessage')->nullable();
            $table->string('owner')->nullable();
            $table->text('desc')->nullable();
            $table->string('creation')->nullable();
            $table->tinyInteger('send')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('grupos');
    }
};
