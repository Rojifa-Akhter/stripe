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
                'user_name' => 'admin',
                'email' => 'admin@gmail.com',
                'phone' => '0987654',
                'password' => bcrypt('12345678'),
                'role' => 'ADMIN',
            ],
            [
                'full_name' => 'user',
                'user_name' => 'user1',
                'email' => 'user@gmail.com',
                'phone' => '098765412', // Add phone number here
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
            [
                'full_name' => 'user1',
                'user_name' => 'user2',
                'email' => 'user1@gmail.com',
                'phone' => '09876541', // Add phone number here
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
            [
                'full_name' => 'user2',
                'user_name' => 'user3',
                'email' => 'user2@gmail.com',
                'phone' => '09876542', // Add phone number here
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
            [
                'full_name' => 'user3',
                'user_name' => 'user4',
                'email' => 'user3@gmail.com',
                'phone' => '09876543', // Add phone number here
                'password' => bcrypt('12345678'),
                'role' => 'USER',
            ],
        ]);
        
    }
}
