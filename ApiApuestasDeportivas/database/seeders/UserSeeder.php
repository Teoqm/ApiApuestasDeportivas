<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@api.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'saldo' => 1000000
        ]);

        User::create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario@api.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USUARIO,
            'saldo' => 500000
        ]);
    }
}