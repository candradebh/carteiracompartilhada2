<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->string('tipoativo');
            $table->integer('ano')->unsigned();
            $table->integer('mes')->unsigned();
            $table->double('compras')->default(0.0);
            $table->double('vendas')->default(0.0);
            $table->double('resultado')->default(0.0);
            $table->double('prejuizoacumulado')->default(0.0);
            $table->double('despesas')->default(0.0);
            $table->double('patrimonio')->default(0.0);
            $table->double('darf')->default(0.0);
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
        Schema::dropIfExists('resultados');
    }
}
