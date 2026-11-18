<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private function categoria(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'ponencias'],
            ['name' => 'Ponencias'],
        );
    }

    // Datos de ejemplo para una entrada.
    private function datos(): array
    {
        return [
            'title' => 'Ponencias del día uno',
            'category_id' => $this->categoria()->id,
            'activity_date' => '2026-11-16',
            'excerpt' => 'Resumen de las ponencias.',
            'content' => 'Contenido completo de la entrada.',
            'status' => 'published',
            'authors' => [User::factory()->create()->id],
        ];
    }

    public function test_un_invitado_no_puede_entrar_al_crud(): void
    {
        $this->get('/posts')->assertRedirect('/login');
        $this->get('/posts/create')->assertRedirect('/login');
    }

    public function test_un_integrante_puede_crear_una_entrada_publicada(): void
    {
        $autor = User::factory()->create();
        $otro = User::factory()->create();

        $response = $this->actingAs($autor)->post('/posts', [
            ...$this->datos(),
            'authors' => [$autor->id, $otro->id],
            'cover_image' => UploadedFile::fake()->create('portada.jpg', 200, 'image/jpeg'),
        ]);

        $response->assertRedirect('/posts');

        $post = Post::firstOrFail();

        $this->assertSame('ponencias-del-dia-uno', $post->slug);
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertSame($autor->id, $post->user_id);
        $this->assertEqualsCanonicalizing([$autor->id, $otro->id], $post->authors->pluck('id')->all());
        Storage::disk('public')->assertExists($post->cover_image);
    }

    public function test_el_titulo_genera_un_slug_unico(): void
    {
        $autor = User::factory()->create();

        $this->actingAs($autor)->post('/posts', $this->datos())->assertRedirect('/posts');
        $this->actingAs($autor)->post('/posts', $this->datos())->assertRedirect('/posts');

        $this->assertSame(
            ['ponencias-del-dia-uno', 'ponencias-del-dia-uno-2'],
            Post::orderBy('id')->pluck('slug')->all(),
        );
    }

    public function test_la_entrada_se_valida(): void
    {
        $autor = User::factory()->create();

        $this->actingAs($autor)
            ->post('/posts', ['title' => '', 'authors' => []])
            ->assertSessionHasErrors(['title', 'category_id', 'activity_date', 'content', 'status', 'authors']);

        $this->assertSame(0, Post::count());
    }

    public function test_al_publicar_un_borrador_se_registra_la_fecha(): void
    {
        $autor = User::factory()->create();
        $datos = $this->datos();

        $this->actingAs($autor)->post('/posts', [
            ...$datos,
            'authors' => [$autor->id],
            'status' => 'draft',
        ])->assertRedirect('/posts');

        $post = Post::firstOrFail();
        $this->assertNull($post->published_at);

        $this->actingAs($autor)->put("/posts/{$post->slug}", [
            ...$datos,
            'authors' => [$autor->id],
            'status' => 'published',
        ])->assertRedirect('/posts');

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_cambiar_el_titulo_no_cambia_el_slug(): void
    {
        $autor = User::factory()->create();
        $datos = $this->datos();

        $this->actingAs($autor)->post('/posts', [
            ...$datos,
            'authors' => [$autor->id],
        ]);

        $post = Post::firstOrFail();

        $this->actingAs($autor)->put("/posts/{$post->slug}", [
            ...$datos,
            'title' => 'Otro título distinto',
            'authors' => [$autor->id],
        ])->assertRedirect('/posts');

        $post->refresh();
        $this->assertSame('Otro título distinto', $post->title);
        $this->assertSame('ponencias-del-dia-uno', $post->slug);
    }

    public function test_las_imagenes_adicionales_se_guardan_y_se_pueden_eliminar(): void
    {
        $autor = User::factory()->create();

        $this->actingAs($autor)->post('/posts', [
            ...$this->datos(),
            'authors' => [$autor->id],
            'images' => [
                UploadedFile::fake()->create('uno.jpg', 200, 'image/jpeg'),
                UploadedFile::fake()->create('dos.jpg', 200, 'image/jpeg'),
            ],
        ])->assertRedirect('/posts');

        $post = Post::firstOrFail();
        $post->load('images');

        $this->assertCount(2, $post->images);
        $this->assertSame([1, 2], $post->images->pluck('sort_order')->all());

        $primera = $post->images->first();

        $this->actingAs($autor)->put("/posts/{$post->slug}", [
            ...$this->datos(),
            'authors' => [$autor->id],
            'remove_images' => [$primera->id],
        ])->assertRedirect('/posts');

        $this->assertSame(1, $post->images()->count());
        Storage::disk('public')->assertMissing($primera->path);
    }

    public function test_al_eliminar_la_entrada_se_borra_su_imagen(): void
    {
        $autor = User::factory()->create();

        $this->actingAs($autor)->post('/posts', [
            ...$this->datos(),
            'authors' => [$autor->id],
            'cover_image' => UploadedFile::fake()->create('portada.jpg', 200, 'image/jpeg'),
        ]);

        $post = Post::firstOrFail();
        $ruta = $post->cover_image;

        $this->actingAs($autor)
            ->delete("/posts/{$post->slug}")
            ->assertRedirect('/posts');

        $this->assertSame(0, Post::count());
        Storage::disk('public')->assertMissing($ruta);
    }
}
