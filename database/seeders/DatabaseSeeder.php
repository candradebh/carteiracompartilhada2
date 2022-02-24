<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /*Admin Seeder*/
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class
        ]);
        \App\Models\User::factory(50)->create();
        \App\Models\DemoContent::factory(100)->create();

        //-- minhas 
        $this->call(AtivosSeeder::class);
        $this->call(CorretoraSeeder::class);
        $this->call(CarteirasSeeder::class);
        $this->call(CambioSeeder::class);
    }
}
