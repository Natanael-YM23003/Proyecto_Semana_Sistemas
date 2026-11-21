<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Evidence;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    private function crearEntrada(User $user, string $titulo = 'Entrada de prueba'): Post
    {
        $categoria = Category::firstOrCreate(
            ['slug' => 'talleres'],
            ['name' => 'Talleres']
        );

        return Post::create([
            'title' => $titulo,
            'slug' => 'entrada-de-prueba-' . uniqid(),
            'category_id' => $categoria->id,
            'user_id' => $user->id,
            'activity_date' => '2026-11-18',
            'excerpt' => 'Resumen de prueba',
            'content' => 'Contenido de la actividad desarrollada.',
            'learnings' => 'Muchos aprendizajes.',
            'reflection' => 'Gran experiencia.',
            'status' => 'published',
        ]);
    }

    public function test_invitado_es_redirigido_al_login_al_intentar_ver_crud(): void
    {
        $this->get('/panel')->assertRedirect('/login');
        $this->get('/panel/crud')->assertRedirect('/login');
        $this->get('/crud')->assertRedirect('/login');
    }

    public function test_usuario_puede_ver_entradas_y_evidencias_en_el_crud(): void
    {
        $user = User::factory()->create();
        $post = $this->crearEntrada($user, 'Taller de Desarrollo Laravel');
        $evidence = Evidence::factory()->create([
            'title' => 'Foto grupal Comelona de Pizza',
            'event_reference' => 'Comelona de pizza',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/panel');

        $response->assertOk();
        $response->assertSee('Panel de Control · CRUD');
        $response->assertSee('Todo el contenido');
        $response->assertSee('Solo Entradas');
        $response->assertSee('Solo Evidencias');
        // Debe ver ambos registros en la vista unificada
        $response->assertSee('Taller de Desarrollo Laravel');
        $response->assertSee('Foto grupal Comelona de Pizza');
        $response->assertSee('Comelona de pizza');
        $response->assertSee('Eliminar');
        $response->assertSee('Editar');
    }

    public function test_se_puede_eliminar_una_entrada_desde_el_crud(): void
    {
        $user = User::factory()->create();
        $post = $this->crearEntrada($user, 'Entrada a eliminar');

        $response = $this->actingAs($user)
            ->from('/panel')
            ->delete('/posts/' . $post->slug);

        $response->assertRedirect('/panel');
        $response->assertSessionHas('status', 'Entrada eliminada correctamente.');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_se_puede_eliminar_una_evidencia_desde_el_crud(): void
    {
        $user = User::factory()->create();
        $evidence = Evidence::factory()->create([
            'title' => 'Evidencia a borrar',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from('/panel')
            ->delete('/evidencias/' . $evidence->id);

        $response->assertRedirect('/panel');
        $response->assertSessionHas('status', 'Evidencia eliminada exitosamente.');
        $this->assertDatabaseMissing('evidences', ['id' => $evidence->id]);
    }

    public function test_buscador_en_el_crud_filtra_entradas_y_evidencias(): void
    {
        $user = User::factory()->create();
        $this->crearEntrada($user, 'Hackathon de Inteligencia Artificial');
        Evidence::factory()->create([
            'title' => 'Torneo de Futbol Sala Final',
            'event_reference' => 'Torneo de fútbol',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/panel?q=Hackathon');
        $response->assertOk();
        $response->assertSee('Hackathon de Inteligencia Artificial');
        $response->assertDontSee('Torneo de Futbol Sala Final');

        $response2 = $this->actingAs($user)->get('/panel?q=Torneo');
        $response2->assertOk();
        $response2->assertSee('Torneo de Futbol Sala Final');
        $response2->assertDontSee('Hackathon de Inteligencia Artificial');
    }
}
