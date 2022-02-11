<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtivosCarteirasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ativos_carteiras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carteira_id');
            $table->foreign('carteira_id')
                    ->references('id')
                    ->on('carteiras');
            $table->unsignedBigInteger('ativo_id');
            $table->foreign('ativo_id')
                    ->references('id')
                    ->on('ativos');
            $table->bigInteger('quantidade')->default(0);
            $table->double('precomedio')->default(0.0);
            $table->double('total')->default(0.0);
            $table->double('totalacumulado')->default(0.0);
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
        Schema::dropIfExists('ativos_carteiras');
    }
}
