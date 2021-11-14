<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserDatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->where('type', \App\Models\User::TYPE_ADMIN)->delete();

        $admin = \App\Models\User::create([
            'username' => 'admin',
            'name' => 'Admin',
            'type' => \App\Models\User::TYPE_ADMIN,
            'password' => Hash::make('123456'),
        ]);

        $user = \App\Models\User::create([
            'username' => 'user',
            'name' => 'User',
            'type' => \App\Models\User::TYPE_USER,
            'password' => Hash::make('123456'),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
