<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ativo_id');
            $table->foreign('ativo_id')
                    ->references('id')
                    ->on('ativos');
            $table->dateTime('data');
            $table->double('open')->default('0.0');
            $table->double('hight')->default('0.0');
            $table->double('low')->default('0.0');
            $table->double('close')->default('0.0');
            $table->double('volume')->default('0.0');
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
        Schema::dropIfExists('cotacoes');
    }
}
