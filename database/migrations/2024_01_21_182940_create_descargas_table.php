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
        Schema::create('descargas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('name')->nullable();
            $table->text('url')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('file')->nullable();
            $table->string('slug')->nullable();
            $table->string('origen')->nullable();
            $table->text('contactos')->nullable();
            $table->text('grupos')->nullable();
            $table->text('message')->nullable();
            $table->double('size')->nullable();
            $table->tinyInteger('send')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('descargas');
    }
};
