<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorretoraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $data = [
            ['nome'=>'Inter','realizaimportacao'=>1,'cambio'=>0 ],
            ['nome'=>'Xp Investimentos','realizaimportacao'=>1 ,'cambio'=>0 ],
            ['nome'=>'Avenue','realizaimportacao'=>1 ,'cambio'=>1 ],
        ];

        DB::table('corretoras')->insert($data);

    }
}
