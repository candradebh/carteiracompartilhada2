<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Actions\Fortify\CreateNewUser;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Carlos Andrade',
                'title' => 'Dono Da porra toda',
                'email' => 'carlinhosandrade_@hotmail.com',
                'password' => bcrypt('cm2209'),
            ],
            [
                'name' => 'Maria Sales',
                'title' => 'Gostosona',
                'email' => 'mariah_xxi@hotmail.com',
                'password' => bcrypt('cm2209'),
            ],
            [
                'name' => 'Marcone',
                'title' => 'Seta longo prazo',
                'email' => 'marconesenna@hotmail.com',
                'password' => bcrypt('cm2209'),
            ],
            [
                'name' => 'Andressa',
                'title' => 'Rica',
                'email' => 'andressarodriguesandrade@gmail.com',
                'password' => bcrypt('cm2209'),
            ]
        ];

        /*Admins*/
        foreach($data as $user){
            tap(User::create($user), function (User $user) {
                (new CreateNewUser())->createTeam($user);
            });
        }


    }
}
