<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => env('TEST_USER_NAME'),
            'email' => env('TEST_USER_EMAIL'),
            'password' => Hash::make(env('TEST_USER_PASSWORD')),
            'role' => env('TEST_USER_ROLE')
        ]);


        User::create([
            'name' => env('TEST_ADMIN_NAME'),
            'email' => env('TEST_ADMIN_EMAIL'),
            'password' => Hash::make(env('TEST_ADMIN_PASSWORD')),
            'role' => env('TEST_ADMIN_ROLE')
        ]);


    }
}
