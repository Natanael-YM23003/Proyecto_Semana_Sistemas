<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get('/panel')->assertRedirect('/login');
    }

    public function test_la_pantalla_de_login_se_muestra(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Ingresar al panel');
    }

    public function test_un_usuario_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create([
            'password' => 'secreta123',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secreta123',
        ]);

        $response->assertRedirect('/panel');
        $this->assertAuthenticatedAs($user);
    }

    public function test_credenciales_incorrectas_no_inician_sesion(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'incorrecta',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_un_usuario_autenticado_no_ve_el_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/panel');
    }

    public function test_el_panel_carga_para_un_usuario_autenticado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/panel')
            ->assertOk()
            ->assertSee('Hola, '.$user->name);
    }

    public function test_un_usuario_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
