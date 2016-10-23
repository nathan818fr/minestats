<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use MineStats\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!User::where('id', '1')->exists()) {
            (new User([
                'id'                   => 1,
                'username'             => 'admin',
                'password'             => Hash::make('password'),
                'must_change_password' => false,
                'acl'                  => User::ACL_OWNER
            ]))->save();
        }
    }
}
