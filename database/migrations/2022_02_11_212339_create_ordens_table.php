<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carteira_id');
            $table->foreign('carteira_id')
                    ->references('id')
                    ->on('carteiras');

            $table->unsignedBigInteger('corretora_id');
            $table->foreign('corretora_id')
                    ->references('id')
                    ->on('corretoras');

            $table->unsignedBigInteger('ativo_id');
            $table->foreign('ativo_id')
                    ->references('id')
                    ->on('ativos');

            $table->char('tipoordem');
            $table->dateTime('data');
            $table->integer('quantidade');
            $table->double('preco');
            $table->double('total');
            $table->double('despesas')->default('0.0');
            $table->double('outras_despesas')->nullable()->default('0.0');
            $table->integer('split_id')->nullable();
            $table->integer('split_quantidade_origem')->nullable();
            $table->double('split_valor_origem')->nullable();//8:1
            $table->date('split_data')->nullable();
            $table->integer('inplit_id')->nullable();
            $table->integer('inplit_quantidade_origem')->nullable();
            $table->double('inplit_valor_origem')->nullable();//8:1
            $table->date('inplit_data')->nullable();
            $table->integer('saldo')->default(0)->nullable();//8:1
            $table->string('origem')->default('MANUAL')->nullable();
            $table->string('path')->nullable();
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
        Schema::dropIfExists('ordens');
    }
}
