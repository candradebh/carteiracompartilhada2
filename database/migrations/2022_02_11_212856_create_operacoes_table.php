<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ativo_id');
            $table->foreign('ativo_id')
                    ->references('id')
                    ->on('ativos');
            $table->string('tipooperacao');//split ou inplit
            $table->string('proporcao')->nullable()->default(0);//1:8 ou 8:1
            $table->double('valor_original')->nullable()->default(0);
            $table->double('valor_alterado')->nullable()->default(0);
            $table->string('novoticker')->nullable()->default('');
            $table->string('novonome')->nullable()->default('');
            $table->string('novocnpj')->nullable()->default('');
            $table->date('data');
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
        Schema::dropIfExists('operacoes');
    }
}
