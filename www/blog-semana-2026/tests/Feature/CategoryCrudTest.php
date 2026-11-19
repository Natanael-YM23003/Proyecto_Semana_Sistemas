<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_un_invitado_no_puede_entrar_al_crud_de_categorias(): void
    {
        $this->get('/categories')->assertRedirect('/login');
        $this->get('/categories/create')->assertRedirect('/login');
    }

    public function test_un_integrante_puede_crear_una_categoria(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->post('/categories', [
                'name' => 'Conferencias',
                'description' => 'Charlas de la semana.',
            ])
            ->assertRedirect('/categories');

        $category = Category::firstOrFail();

        $this->assertSame('Conferencias', $category->name);
        $this->assertSame('conferencias', $category->slug);
        $this->assertSame('Charlas de la semana.', $category->description);
    }

    public function test_el_nombre_de_la_categoria_se_valida(): void
    {
        $usuario = User::factory()->create();

        Category::create([
            'name' => 'Ponencias',
            'slug' => 'ponencias',
        ]);

        $this->actingAs($usuario)
            ->post('/categories', ['name' => ''])
            ->assertSessionHasErrors(['name']);

        $this->actingAs($usuario)
            ->post('/categories', ['name' => 'Ponencias'])
            ->assertSessionHasErrors(['name']);

        $this->assertSame(1, Category::count());
    }

    public function test_dos_nombres_parecidos_no_repiten_el_slug(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->post('/categories', ['name' => 'Fútbol'])->assertRedirect('/categories');
        $this->actingAs($usuario)->post('/categories', ['name' => 'Futbol'])->assertRedirect('/categories');

        $this->assertSame(
            ['futbol', 'futbol-2'],
            Category::orderBy('id')->pluck('slug')->all(),
        );
    }

    public function test_una_categoria_se_puede_editar(): void
    {
        $usuario = User::factory()->create();

        $category = Category::create([
            'name' => 'Taller',
            'slug' => 'taller',
        ]);

        $this->actingAs($usuario)
            ->put("/categories/{$category->slug}", [
                'name' => 'Talleres',
                'description' => 'Actividades prácticas.',
            ])
            ->assertRedirect('/categories');

        $category->refresh();

        $this->assertSame('Talleres', $category->name);
        $this->assertSame('taller', $category->slug);
        $this->assertSame('Actividades prácticas.', $category->description);
    }

    public function test_una_categoria_sin_entradas_se_puede_eliminar(): void
    {
        $usuario = User::factory()->create();

        $category = Category::create([
            'name' => 'Temporal',
            'slug' => 'temporal',
        ]);

        $this->actingAs($usuario)
            ->delete("/categories/{$category->slug}")
            ->assertRedirect('/categories');

        $this->assertSame(0, Category::count());
    }

    public function test_no_se_puede_eliminar_una_categoria_con_entradas(): void
    {
        $usuario = User::factory()->create();

        $category = Category::create([
            'name' => 'Ponencias',
            'slug' => 'ponencias',
        ]);

        Post::create([
            'category_id' => $category->id,
            'user_id' => $usuario->id,
            'title' => 'Entrada de prueba',
            'slug' => 'entrada-de-prueba',
            'activity_date' => '2026-11-16',
            'content' => 'Contenido.',
            'learnings' => 'Aprendizajes.',
            'reflection' => 'Reflexión.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($usuario)
            ->delete("/categories/{$category->slug}")
            ->assertRedirect('/categories');

        $this->assertSame(1, Category::count());
        $this->assertSame(1, Post::count());
    }
}
