<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentaDeCartasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venta_de_cartas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_carta');
            $table->foreign('id_carta')->references('id')->on('coleccion_cartas');
            $table->string('cantidad'); 
            $table->string('precio');
            $table->unsignedBigInteger('usuario');
            $table->foreign('usuario')->references('id')->on('users');
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
        Schema::dropIfExists('venta_de_cartas');
    }
}
