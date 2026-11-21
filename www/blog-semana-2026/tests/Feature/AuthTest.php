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
            ->assertSee('Panel de Control')
            ->assertSee('Cerrar sesión');
    }

    public function test_un_usuario_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_la_pantalla_de_registro_se_muestra(): void
    {
        $this->get('/registro')
            ->assertOk()
            ->assertSee('Crear una cuenta');
    }

    public function test_un_usuario_puede_registrarse(): void
    {
        $response = $this->post('/registro', [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@ues.edu.sv',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/panel');
        $this->assertDatabaseHas('users', [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@ues.edu.sv',
        ]);
        $this->assertAuthenticated();
    }

    public function test_el_registro_falla_si_los_datos_no_son_validos(): void
    {
        $this->from('/registro')
            ->post('/registro', [
                'name' => '',
                'email' => 'correo-invalido',
                'password' => '123',
                'password_confirmation' => '456',
            ])
            ->assertRedirect('/registro')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_el_registro_falla_si_el_correo_ya_esta_registrado(): void
    {
        User::factory()->create([
            'email' => 'repetido@ues.edu.sv',
        ]);

        $this->from('/registro')
            ->post('/registro', [
                'name' => 'Otro Nombre',
                'email' => 'repetido@ues.edu.sv',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect('/registro')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_un_usuario_autenticado_no_ve_el_registro(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/registro')
            ->assertRedirect('/panel');
    }
}
