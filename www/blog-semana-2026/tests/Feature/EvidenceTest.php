<?php

namespace Tests\Feature;

use App\Models\Evidence;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_la_pagina_de_evidencias_se_muestra_publicamente(): void
    {
        Evidence::factory()->create([
            'title' => 'Foto Ponencia 1',
            'event_reference' => 'Ponencia 1',
        ]);

        $this->get('/evidencias')
            ->assertOk()
            ->assertSee('Evidencias')
            ->assertSee('Foto Ponencia 1')
            ->assertSee('Ponencia 1');
    }

    public function test_un_invitado_no_puede_acceder_al_formulario_de_crear_evidencia(): void
    {
        $this->get('/evidencias/crear')
            ->assertRedirect('/login');
    }

    public function test_un_usuario_autenticado_puede_ver_formulario_de_crear_evidencia(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/evidencias/crear')
            ->assertOk()
            ->assertSee('Nueva evidencia')
            ->assertSee('Referencia del evento')
            ->assertSee('Ponencia 1')
            ->assertSee('Torneo de fútbol');
    }

    public function test_un_usuario_puede_crear_una_evidencia(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('evidencia.jpg', 600, 400);

        $response = $this->actingAs($user)
            ->post('/evidencias', [
                'title' => 'Premiación del Torneo de Fútbol',
                'event_reference' => 'Torneo de fútbol',
                'image' => $file,
                'description' => 'Entrega de trofeos al equipo campeón.',
                'taken_at' => '2026-11-18',
            ]);

        $response->assertRedirect('/evidencias');
        $response->assertSessionHas('status', 'Evidencia agregada exitosamente.');

        $this->assertDatabaseHas('evidences', [
            'title' => 'Premiación del Torneo de Fútbol',
            'event_reference' => 'Torneo de fútbol',
            'user_id' => $user->id,
            'description' => 'Entrega de trofeos al equipo campeón.',
        ]);

        $evidence = Evidence::where('title', 'Premiación del Torneo de Fútbol')->first();
        $this->assertNotNull($evidence);
        Storage::disk('public')->assertExists($evidence->path);
    }

    public function test_creacion_falla_sin_campos_obligatorios(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/evidencias/crear')
            ->post('/evidencias', [
                'title' => '',
                'event_reference' => '',
            ])
            ->assertRedirect('/evidencias/crear')
            ->assertSessionHasErrors(['title', 'event_reference', 'image']);
    }

    public function test_se_puede_filtrar_evidencias_por_evento(): void
    {
        Evidence::factory()->create([
            'title' => 'Foto de Taller Docker',
            'event_reference' => 'Taller',
        ]);

        Evidence::factory()->create([
            'title' => 'Foto de Comelona',
            'event_reference' => 'Comelona de pizza',
        ]);

        $response = $this->get('/evidencias?evento=Taller');
        $response->assertOk();
        $response->assertSee('Foto de Taller Docker');
        $response->assertDontSee('Foto de Comelona');
    }

    public function test_un_usuario_puede_editar_una_evidencia(): void
    {
        $user = User::factory()->create();
        $evidence = Evidence::factory()->create([
            'title' => 'Título viejo',
            'event_reference' => 'Ponencia 2',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put('/evidencias/'.$evidence->id, [
                'title' => 'Título actualizado',
                'event_reference' => 'Ponencia 3',
                'description' => 'Nueva descripción',
            ]);

        $response->assertRedirect('/evidencias');
        $this->assertDatabaseHas('evidences', [
            'id' => $evidence->id,
            'title' => 'Título actualizado',
            'event_reference' => 'Ponencia 3',
        ]);
    }

    public function test_un_usuario_puede_eliminar_una_evidencia(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('foto_a_borrar.jpg');
        $path = $file->store('evidences', 'public');

        $evidence = Evidence::factory()->create([
            'user_id' => $user->id,
            'path' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($user)
            ->delete('/evidencias/'.$evidence->id);

        $response->assertRedirect('/evidencias');
        $this->assertDatabaseMissing('evidences', ['id' => $evidence->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_el_boton_evidencias_aparece_en_vistas_de_entradas(): void
    {
        $user = User::factory()->create();

        // En panel de entradas
        $this->actingAs($user)
            ->get('/posts')
            ->assertOk()
            ->assertSee('Evidencias');

        // En vista pública de entradas
        $this->get('/entradas')
            ->assertOk()
            ->assertSee('Evidencias');
    }
}
