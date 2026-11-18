<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categorías que corresponden a las actividades de la semana.
     */
    public function run(): void
    {
        $categorias = [
            [
                'name' => 'Ponencias',
                'slug' => 'ponencias',
                'description' => 'Charlas y conferencias de la Semana de Sistemas 2026.',
            ],
            [
                'name' => 'Talleres',
                'slug' => 'talleres',
                'description' => 'Talleres prácticos, herramientas y resultados.',
            ],
            [
                'name' => 'Bingo',
                'slug' => 'bingo',
                'description' => 'Dinámica, experiencia y participación.',
            ],
            [
                'name' => 'Feria de proyectos',
                'slug' => 'feria-de-proyectos',
                'description' => 'Proyectos visitados, tecnologías e innovación.',
            ],
            [
                'name' => 'Comelona de pizza',
                'slug' => 'comelona-de-pizza',
                'description' => 'Desarrollo, convivencia y experiencia.',
            ],
            [
                'name' => 'Fútbol sala',
                'slug' => 'futbol-sala',
                'description' => 'Desarrollo, equipos y participación.',
            ],
            [
                'name' => 'Parrillada',
                'slug' => 'parrillada',
                'description' => 'Descripción, convivencia y reflexión.',
            ],
        ];

        foreach ($categorias as $categoria) {
            Category::updateOrCreate(['slug' => $categoria['slug']], $categoria);
        }
    }
}
