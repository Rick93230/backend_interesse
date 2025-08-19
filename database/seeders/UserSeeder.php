<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
        ]);

        User::factory()->create([
            'name' => 'Basic User 1',
            'email' => 'basic1@example.com',
            'password' => bcrypt('basic1'),
        ]);

        User::factory()->create([
            'name' => 'Basic User 2',
            'email' => 'basic2@example.com',
            'password' => bcrypt('basic2'),
        ]);

        User::factory()->create([
            'name' => 'Basic User 3',
            'email' => 'basic3@example.com',
            'password' => bcrypt('basic3'),
        ]);

    }
}
