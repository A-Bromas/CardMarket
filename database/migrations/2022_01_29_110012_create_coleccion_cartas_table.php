<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColeccionCartasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coleccion_cartas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_carta');
            $table->unsignedBigInteger('id_coleccion');
            $table->foreign('id_carta')->references('id')->on('cartas');
            $table->foreign('id_coleccion')->references('id')->on('coleccions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coleccion_cartas');
    }
}
