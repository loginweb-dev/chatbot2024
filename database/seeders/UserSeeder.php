<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Command :
         * artisan seed:generate User
         *
         */

        
        $newData0 = \App\Models\User::create([
            'id' => 1,
            'role_id' => 1,
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'avatar' => 'users/default.png',
            'email_verified_at' => NULL,
            'password' => '$2y$10$Jj37/UCrPhz4daNy9m3RUOrVaqdzs38vRDnwmCPyDOa48d7C7oiL.',
            'remember_token' => 'Msv41sdOBATLcaCGSdz8OjhU1A2RguDiTJC5UMNqShfSaNGXMje6s4Q2FRFB',
            'settings' => NULL,
            'created_at' => '2023-11-26 16:23:41',
            'updated_at' => '2023-11-26 16:23:41',
        ]);
        $newData1 = \App\Models\User::create([
            'id' => 3,
            'role_id' => 3,
            'name' => 'Percy Alvarez',
            'email' => 'percyalvarez@botcenter.lat',
            'avatar' => 'users/default.png',
            'email_verified_at' => NULL,
            'password' => '$2y$10$jHbqm.a2At2pzTS7g6lEze4vcxWGU5qN/mVji1QVyx2lciiwI0vr2',
            'remember_token' => 'ObgqjwzK9WkiU4qCllgEIXGID10dblI15r6KeyQOUg5zliiJnMsXCKGSId87',
            'settings' => '{"locale":"es"}',
            'created_at' => '2023-12-22 20:31:40',
            'updated_at' => '2023-12-24 06:24:15',
        ]);
        $newData2 = \App\Models\User::create([
            'id' => 4,
            'role_id' => 3,
            'name' => 'Paul Muiba',
            'email' => 'paulmuiba@botcenter.lat',
            'avatar' => 'users/default.png',
            'email_verified_at' => NULL,
            'password' => '$2y$10$fn1VCEuDQVIbEunnNPUnFu4Enc4.3IhENtl3FoepQpFLkGkJorkHm',
            'remember_token' => 'OLcTiKD6o3zD8Quab9ZGbv9Xqcolpuf9iO30cUD10w2buONwM41vfBYINtvj',
            'settings' => '{"locale":"es"}',
            'created_at' => '2023-12-22 20:32:01',
            'updated_at' => '2023-12-23 19:26:44',
        ]);
    }
}