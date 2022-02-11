<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carteira_id');
            $table->foreign('carteira_id')
                    ->references('id')
                    ->on('carteiras');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                    ->references('id')
                    ->on('users');

            $table->unsignedBigInteger('corretora_id');
            $table->foreign('corretora_id')
                    ->references('id')
                    ->on('corretoras');

            $table->dateTime('data');
            $table->string('origem');
            $table->double('reais');
            $table->double('dolar');
            $table->double('cotacao');
            $table->double('taxas');
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
        Schema::dropIfExists('exchanges');
    }
}
