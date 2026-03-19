<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['username' => 'teacher1'],
            [
                'password' => Hash::make('123456a@A'),
                'name' => 'Giáo viên 1',
                'role' => 'teacher',
            ]
        );

        User::firstOrCreate(
            ['username' => 'teacher2'],
            [
                'password' => Hash::make('123456a@A'),
                'name' => 'Giáo viên 2',
                'role' => 'teacher',
            ]
        );

        User::firstOrCreate(
            ['username' => 'student1'],
            [
                'password' => Hash::make('123456a@A'),
                'name' => 'Sinh viên 1',
                'role' => 'student',
            ]
        );

        User::firstOrCreate(
            ['username' => 'student2'],
            [
                'password' => Hash::make('123456a@A'),
                'name' => 'Sinh viên 2',
                'role' => 'student',
            ]
        );
    }
}
