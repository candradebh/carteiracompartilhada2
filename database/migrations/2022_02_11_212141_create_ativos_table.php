<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtivosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ativos', function (Blueprint $table) {
            $table->id();
            $table->string('ticker')->unique();
            $table->string('nome');
            $table->string('cnpj')->nullable();
            $table->string('setor');
            $table->string('classe');//PN | ON
            $table->string('categoria');//Açoes | Fundos Imobiliários | ETF
            $table->double('cotacao')->default(0.0);
            $table->dateTime('dataCotacao')->nullable();
            $table->string('xpimport')->nullable();
            $table->date('dataAnalise')->nullable()->default(null);
            $table->double('mm14')->nullable()->default(0.0);
            $table->double('mm30')->nullable()->default(0.0);
            $table->double('mm180')->nullable()->default(0.0);
            $table->double('mm365')->nullable()->default(0.0);
            $table->double('mm730')->nullable()->default(0.0);
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
        Schema::dropIfExists('ativos');
    }
}
