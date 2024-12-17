<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'full_name' => 'admin',
                'user_name' => 'admin', // Provide user_name
                'email' => 'admin@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => 'ADMIN',
            ],
            [
                'full_name' => 'user',
                'user_name' => 'user1', // Provide user_name
                'email' => 'user@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
            [
                'full_name' => 'user1',
                'user_name' => 'user2', // Provide user_name
                'email' => 'user1@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
            [
                'full_name' => 'user2',
                'user_name' => 'user3', // Provide user_name
                'email' => 'user2@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
            [
                'full_name' => 'user3',
                'user_name' => 'user4', // Provide user_name
                'email' => 'user3@gmail.com',
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
        ]);
    }
}
