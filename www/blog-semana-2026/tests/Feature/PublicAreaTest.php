<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function categoria(string $name = 'Ponencias', string $slug = 'ponencias'): Category
    {
        return Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
    }

    private function entrada(array $cambios = []): Post
    {
        $categoria = $this->categoria();
        $autor = User::factory()->create();

        $post = Post::create([
            'category_id' => $categoria->id,
            'user_id' => $autor->id,
            'title' => 'Ponencia de prueba',
            'slug' => 'ponencia-de-prueba',
            'activity_date' => '2026-11-16',
            'excerpt' => 'Resumen de la ponencia.',
            'content' => 'Contenido de la ponencia.',
            'learnings' => 'Aprendizajes de la ponencia.',
            'reflection' => 'Reflexión de la ponencia.',
            'status' => 'published',
            'published_at' => now(),
            ...$cambios,
        ]);

        $post->authors()->attach($autor);

        return $post;
    }

    public function test_la_portada_muestra_solo_entradas_publicadas(): void
    {
        $this->entrada(['title' => 'Entrada publicada', 'slug' => 'entrada-publicada']);
        $this->entrada(['title' => 'Entrada en borrador', 'slug' => 'entrada-en-borrador', 'status' => 'draft']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Entrada publicada')
            ->assertDontSee('Entrada en borrador');
    }

    public function test_el_listado_ordena_de_la_mas_reciente_a_la_mas_antigua(): void
    {
        $this->entrada([
            'title' => 'Actividad del primer dia',
            'slug' => 'actividad-del-primer-dia',
            'activity_date' => '2026-11-16',
        ]);

        $this->entrada([
            'title' => 'Actividad del segundo dia',
            'slug' => 'actividad-del-segundo-dia',
            'activity_date' => '2026-11-17',
        ]);

        $this->get('/entradas')
            ->assertOk()
            ->assertSeeInOrder(['Actividad del segundo dia', 'Actividad del primer dia']);
    }

    public function test_el_detalle_muestra_la_informacion_completa(): void
    {
        $autor = User::factory()->create(['name' => 'Carlos']);
        $post = $this->entrada([
            'speaker' => 'Ing. Laura Méndez',
            'learnings' => 'Aprendimos sobre arquitectura MVC.',
            'reflection' => 'Fue una actividad muy útil.',
        ]);
        $post->authors()->attach($autor);

        $this->get("/entradas/{$post->slug}")
            ->assertOk()
            ->assertSee('Ponencia de prueba')
            ->assertSee('Ing. Laura Méndez')
            ->assertSee('Contenido de la ponencia.')
            ->assertSee('Aprendimos sobre arquitectura MVC.')
            ->assertSee('Fue una actividad muy útil.')
            ->assertSee('Carlos');
    }

    public function test_un_borrador_no_se_puede_ver_en_el_detalle(): void
    {
        $post = $this->entrada([
            'title' => 'Borrador privado',
            'slug' => 'borrador-privado',
            'status' => 'draft',
        ]);

        $this->get("/entradas/{$post->slug}")->assertNotFound();
    }

    public function test_la_busqueda_filtra_por_titulo_y_contenido(): void
    {
        $this->entrada([
            'title' => 'Taller de Docker',
            'slug' => 'taller-de-docker',
            'content' => 'Aprendimos a crear contenedores.',
        ]);

        $this->entrada([
            'title' => 'Feria de proyectos',
            'slug' => 'feria-de-proyectos',
            'content' => 'Visitamos los proyectos de los compañeros.',
        ]);

        $this->get('/entradas?buscar=docker')
            ->assertOk()
            ->assertSee('Taller de Docker')
            ->assertDontSee('Feria de proyectos');

        $this->get('/entradas?buscar=contenedores')
            ->assertOk()
            ->assertSee('Taller de Docker')
            ->assertDontSee('Feria de proyectos');
    }

    public function test_se_pueden_filtrar_las_entradas_por_categoria(): void
    {
        $ponencias = $this->categoria();
        $talleres = $this->categoria('Talleres', 'talleres');

        $this->entrada([
            'title' => 'Ponencia sobre redes',
            'slug' => 'ponencia-sobre-redes',
            'category_id' => $ponencias->id,
        ]);

        $this->entrada([
            'title' => 'Taller de Laravel',
            'slug' => 'taller-de-laravel',
            'category_id' => $talleres->id,
        ]);

        $this->get('/entradas?categoria=talleres')
            ->assertOk()
            ->assertSee('Taller de Laravel')
            ->assertDontSee('Ponencia sobre redes');
    }

    public function test_una_categoria_desconocida_devuelve_404(): void
    {
        $this->get('/entradas?categoria=no-existe')->assertNotFound();
    }

    public function test_el_detalle_muestra_las_imagenes_adicionales(): void
    {
        Storage::fake('public');

        $post = $this->entrada();
        $ruta = UploadedFile::fake()->create('galeria.jpg', 100, 'image/jpeg')
            ->store('posts/gallery', 'public');

        PostImage::create([
            'post_id' => $post->id,
            'path' => $ruta,
            'sort_order' => 1,
        ]);

        $this->get("/entradas/{$post->slug}")
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($ruta));
    }

    public function test_el_listado_no_muestra_borradores(): void
    {
        $this->entrada(['title' => 'Publicada', 'slug' => 'publicada']);
        $this->entrada(['title' => 'Oculta', 'slug' => 'oculta', 'status' => 'draft']);

        $this->get('/entradas')
            ->assertOk()
            ->assertSee('Publicada')
            ->assertDontSee('Oculta');
    }
}
