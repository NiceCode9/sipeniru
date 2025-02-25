<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin'),
                'nip' => '1234567890',
            ],
        ];

        // Add 10 teachers
        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'name' => fake('id_ID')->name(),
                'email' => "guru{$i}@gmail.com",
                'password' => bcrypt("guru{$i}"),
                'nip' => fake()->numerify('##########'), // Generate 10 digit NIP
            ];
        }

        foreach ($data as $user) {
            User::create($user);
        }
    }
}
