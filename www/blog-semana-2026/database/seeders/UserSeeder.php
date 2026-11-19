<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    // Integrantes del equipo.
    public function run(): void
    {
        $usuarios = [
            [
                'name' => 'Natanael',
                'email' => 'ym23003@ues.edu.sv',
                'password' => '12345678',
            ],
            [
                'name' => 'Carlos',
                'email' => 'bl23012@ues.edu.sv',
                'password' => '12345678',
            ],
            [
                'name' => 'Dariel',
                'email' => 'ml23011@ues.edu.sv',
                'password' => '12345678',
            ],
        ];

        foreach ($usuarios as $usuario) {
            User::updateOrCreate(
                ['email' => $usuario['email']],
                $usuario,
            );
        }
    }
}
