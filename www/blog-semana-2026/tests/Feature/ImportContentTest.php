<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportContentTest extends TestCase
{
    use RefreshDatabase;

    private string $carpeta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
        $this->carpeta = sys_get_temp_dir().'/contenido-'.uniqid();

        File::ensureDirectoryExists($this->carpeta.'/imagenes');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->carpeta);

        parent::tearDown();
    }

    private function usuarios(): void
    {
        User::factory()->create(['name' => 'Natanael', 'email' => 'ym23003@ues.edu.sv']);
        User::factory()->create(['name' => 'Carlos', 'email' => 'bl23012@ues.edu.sv']);
    }

    private function categorias(): void
    {
        Category::create(['name' => 'Ponencias', 'slug' => 'ponencias']);
        Category::create(['name' => 'Talleres', 'slug' => 'talleres']);
    }

    private function entrada(array $cambios = []): array
    {
        return [
            'slug' => 'ponencias-dia-1',
            'titulo' => 'Ponencias del día 1',
            'categoria' => 'Ponencias',
            'fecha' => '2026-11-16',
            'estado' => 'publicada',
            'resumen' => 'Resumen de las ponencias.',
            'expositor' => 'Ing. Laura Méndez',
            'desarrollo' => 'Desarrollo de la actividad.',
            'aprendizajes' => 'Aprendizajes de la actividad.',
            'reflexion' => 'Reflexión personal.',
            'autores' => ['ym23003@ues.edu.sv', 'bl23012@ues.edu.sv'],
            'portada' => 'imagenes/portada.png',
            'galeria' => ['imagenes/uno.png', 'imagenes/dos.png'],
            ...$cambios,
        ];
    }

    private function archivo(array $entradas): string
    {
        $ruta = $this->carpeta.'/dia-1.json';

        File::put($ruta, json_encode(
            ['entradas' => $entradas],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        ));

        return $ruta;
    }

    private function imagenes(): void
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        foreach (['portada.png', 'uno.png', 'dos.png'] as $nombre) {
            File::put($this->carpeta.'/imagenes/'.$nombre, $png);
        }
    }

    public function test_importa_una_entrada_con_autores_e_imagenes(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $this->artisan('contenido:importar', ['archivo' => $this->archivo([$this->entrada()])])
            ->assertSuccessful();

        $post = Post::where('slug', 'ponencias-dia-1')->firstOrFail();

        $this->assertSame('Ponencias del día 1', $post->title);
        $this->assertSame('published', $post->status);
        $this->assertSame('Ing. Laura Méndez', $post->speaker);
        $this->assertSame('Aprendizajes de la actividad.', $post->learnings);
        $this->assertSame('Reflexión personal.', $post->reflection);
        $this->assertCount(2, $post->authors);
        $this->assertSame(2, $post->images()->count());

        Storage::disk('public')->assertExists('posts/covers/ponencias-dia-1.png');
        Storage::disk('public')->assertExists('posts/gallery/ponencias-dia-1-1.png');
        Storage::disk('public')->assertExists('posts/gallery/ponencias-dia-1-2.png');
    }

    public function test_volver_a_importar_actualiza_sin_duplicar(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $ruta = $this->archivo([$this->entrada()]);

        $this->artisan('contenido:importar', ['archivo' => $ruta])->assertSuccessful();

        $ruta = $this->archivo([
            $this->entrada([
                'titulo' => 'Ponencias del día 1 (corregido)',
                'reflexion' => 'Reflexión corregida.',
                'estado' => 'borrador',
            ]),
        ]);

        $this->artisan('contenido:importar', ['archivo' => $ruta])->assertSuccessful();

        $this->assertSame(1, Post::count());

        $post = Post::firstOrFail();

        $this->assertSame('Ponencias del día 1 (corregido)', $post->title);
        $this->assertSame('Reflexión corregida.', $post->reflection);
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
    }

    public function test_importa_varias_entradas_del_mismo_archivo(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $this->artisan('contenido:importar', [
            'archivo' => $this->archivo([
                $this->entrada(),
                $this->entrada([
                    'slug' => 'talleres-dia-1',
                    'titulo' => 'Talleres del día 1',
                    'categoria' => 'Talleres',
                ]),
            ]),
        ])->assertSuccessful();

        $this->assertSame(2, Post::count());
        $this->assertDatabaseHas('posts', ['slug' => 'talleres-dia-1']);
    }

    public function test_el_estado_por_defecto_es_borrador(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $entrada = $this->entrada();
        unset($entrada['estado']);

        $this->artisan('contenido:importar', ['archivo' => $this->archivo([$entrada])])
            ->assertSuccessful();

        $post = Post::firstOrFail();

        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
    }

    public function test_falla_si_la_categoria_no_existe(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $this->artisan('contenido:importar', [
            'archivo' => $this->archivo([$this->entrada(['categoria' => 'No existe'])]),
        ])
            ->expectsOutputToContain('no existe la categoría')
            ->assertFailed();

        $this->assertSame(0, Post::count());
    }

    public function test_falla_si_un_autor_no_existe(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $this->artisan('contenido:importar', [
            'archivo' => $this->archivo([$this->entrada(['autores' => ['nadie@ues.edu.sv']])]),
        ])
            ->expectsOutputToContain('no existen estos usuarios')
            ->assertFailed();

        $this->assertSame(0, Post::count());
    }

    public function test_falla_si_falta_un_campo_obligatorio(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $entrada = $this->entrada();
        unset($entrada['reflexion']);

        $this->artisan('contenido:importar', ['archivo' => $this->archivo([$entrada])])
            ->expectsOutputToContain('reflexión es obligatorio')
            ->assertFailed();

        $this->assertSame(0, Post::count());
    }

    public function test_la_galeria_se_reemplaza_al_volver_a_importar(): void
    {
        $this->usuarios();
        $this->categorias();
        $this->imagenes();

        $ruta = $this->archivo([$this->entrada()]);
        $this->artisan('contenido:importar', ['archivo' => $ruta])->assertSuccessful();

        $post = Post::firstOrFail();
        $this->assertSame(2, $post->images()->count());

        $ruta = $this->archivo([$this->entrada(['galeria' => []])]);
        $this->artisan('contenido:importar', ['archivo' => $ruta])->assertSuccessful();

        $this->assertSame(0, $post->images()->count());
        Storage::disk('public')->assertMissing('posts/gallery/ponencias-dia-1-1.png');
    }
}
