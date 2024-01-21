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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('contacto_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->date('expiration')->nullable();
            $table->integer('credit')->nullable();
            $table->integer('package_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('credentials')->nullable();
            $table->double('price')->nullable();
            $table->double('venta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
