<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarteiraanualirpfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carteiraanualirpf', function (Blueprint $table) {
            $table->id();
            $table->integer('ano')->unsigned();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->unsignedBigInteger('ativo_id');
            $table->foreign('ativo_id')
                    ->references('id')
                    ->on('ativos')
                    ->onDelete('cascade');
            $table->string('ticker')->nullable();
            $table->string('cnpj')->nullable();
            $table->bigInteger('quantidade')->default(0);
            $table->double('precomedio')->default(0.0);
            $table->double('total')->default(0.0);
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
        Schema::dropIfExists('carteiraanualirpf');
    }
}
