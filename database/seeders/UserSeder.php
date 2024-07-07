<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'ID' => 1,
                'Name' => 'John Doe',
                'Email' => 'john@example.com',
                'Password' => bcrypt('password123'),
                'Phone_Number' => '1234567890',
                'Address' => '123 Main St',
                'City' => 'New York',
                'Status' => 'responsible',
            ],
            [
                'ID' => 2,
                'Name' => 'Jane Smith',
                'Email' => 'jane@example.com',
                'Password' => bcrypt('letmein'),
                'Phone_Number' => '9876543210',
                'Address' => '456 Elm St',
                'City' => 'Los Angeles',
                'Status' => 'responsible',
            ],
        ]);
    }
}
