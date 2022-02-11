<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CambioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $data = [

            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2019-05-10', 'origem'=>'BRL','reais'=>1593.94, 'dolar'=>392.5, 'taxas'=>6.06 , 'cotacao'=>4.061],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2019-05-24', 'origem'=>'BRL','reais'=>2988.64, 'dolar'=>724.12, 'taxas'=>11.36 , 'cotacao'=>4.1273],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2019-08-06', 'origem'=>'USD','reais'=>4091.65, 'dolar'=>1060.95, 'taxas'=>15.61 , 'cotacao'=>3.8713],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-04-06', 'origem'=>'BRL','reais'=>4263.8, 'dolar'=>789.3, 'taxas'=>16.2 , 'cotacao'=>5.402],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-04-15', 'origem'=>'BRL','reais'=>4981.07, 'dolar'=>929.3, 'taxas'=>18.93 , 'cotacao'=>5.36],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-04-20', 'origem'=>'BRL','reais'=>8866.31, 'dolar'=>1643.55, 'taxas'=>33.69 , 'cotacao'=>5.3946],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-04-24', 'origem'=>'BRL','reais'=>5778.04, 'dolar'=>1013.11, 'taxas'=>21.96 , 'cotacao'=>5.7033],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-04-30', 'origem'=>'BRL','reais'=>4981.07, 'dolar'=>893.34, 'taxas'=>18.93 , 'cotacao'=>5.57],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-05-13', 'origem'=>'BRL','reais'=>4981.07, 'dolar'=>818.85, 'taxas'=>18.93 , 'cotacao'=>6.083],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-05-27', 'origem'=>'BRL','reais'=>3486.75, 'dolar'=>641.08, 'taxas'=>13.25 , 'cotacao'=>5.43],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-06-12', 'origem'=>'BRL','reais'=>2988.64, 'dolar'=>577.07, 'taxas'=>11.36 , 'cotacao'=>5.179],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-08-04', 'origem'=>'BRL','reais'=>1593.94, 'dolar'=>291.72, 'taxas'=>6.06 , 'cotacao'=>5.46],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-11-18', 'origem'=>'BRL','reais'=>1354.85, 'dolar'=>248.39, 'taxas'=>5.15 , 'cotacao'=>5.4545],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-11-18', 'origem'=>'BRL','reais'=>4981.07, 'dolar'=>916.21, 'taxas'=>18.93 , 'cotacao'=>5.4366],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2020-12-10', 'origem'=>'BRL','reais'=>5578.8, 'dolar'=>1071.34, 'taxas'=>21.2 , 'cotacao'=>5.2073],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2021-01-19', 'origem'=>'USD','reais'=>730.02, 'dolar'=>140.09, 'taxas'=>2.78 , 'cotacao'=>5.2309],
            ['corretora_id'=>3,'carteira_id'=>3,'user_id'=>1,'data'=>'2021-01-21', 'origem'=>'USD','reais'=>4833.74, 'dolar'=>940.08, 'taxas'=>18.44 , 'cotacao'=>5.1575],


        ];

        DB::table('exchanges')->insert($data);

    }
}
