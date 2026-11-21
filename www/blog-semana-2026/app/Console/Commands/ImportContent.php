<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;
use Throwable;

class ImportContent extends Command
{
    protected $signature = 'contenido:importar {archivo? : Archivo JSON, por ejemplo contenido/dia-1.json}';

    protected $description = 'Carga las entradas del blog desde los archivos JSON de contenido/';

    // Slugs que ya se usaron en esta corrida.
    private array $slugs = [];

    public function handle(): int
    {
        $this->slugs = [];

        $base = base_path('contenido');
        $archivo = $this->argument('archivo');

        if ($archivo) {
            $ruta = $this->rutaArchivo($archivo, $base);

            if (! File::isFile($ruta)) {
                $this->error("No existe el archivo {$ruta}.");

                return self::FAILURE;
            }

            $archivos = [$ruta];
            $carpetaBase = dirname($ruta);
        } else {
            if (! File::isDirectory($base)) {
                $this->error('No existe la carpeta contenido/.');

                return self::FAILURE;
            }

            $archivos = collect(File::glob($base.'/*.json'))
                ->reject(fn (string $ruta) => Str::startsWith(basename($ruta), '_'))
                ->sort()
                ->values()
                ->all();

            if ($archivos === []) {
                $this->warn('No hay archivos JSON para importar en contenido/.');

                return self::SUCCESS;
            }

            $carpetaBase = $base;
        }

        try {
            DB::transaction(function () use ($archivos, $carpetaBase) {
                foreach ($archivos as $ruta) {
                    $this->importarArchivo($ruta, $carpetaBase);
                }
            });
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('No se importó nada: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Contenido importado correctamente.');

        return self::SUCCESS;
    }

    private function importarArchivo(string $ruta, string $carpetaBase): void
    {
        $datos = json_decode(File::get($ruta), true);

        if (! is_array($datos) || ! isset($datos['entradas']) || ! is_array($datos['entradas'])) {
            throw new RuntimeException(basename($ruta).': el JSON debe tener una lista "entradas".');
        }

        $this->line('Archivo '.basename($ruta).': '.count($datos['entradas']).' entrada(s).');

        foreach (array_values($datos['entradas']) as $i => $entrada) {
            if (! is_array($entrada)) {
                throw new RuntimeException(basename($ruta).', entrada '.($i + 1).': debe ser un objeto.');
            }

            $this->importarEntrada($entrada, $i + 1, basename($ruta), $carpetaBase);
        }
    }

    private function importarEntrada(array $entrada, int $numero, string $archivo, string $carpetaBase): void
    {
        $validador = Validator::make($entrada, [
            'slug' => ['nullable', 'string', 'max:180'],
            'titulo' => ['required', 'string', 'max:150'],
            'categoria' => ['required', 'string', 'max:80'],
            'fecha' => ['required', 'date'],
            'estado' => ['nullable', 'in:publicada,borrador,published,draft'],
            'resumen' => ['nullable', 'string', 'max:300'],
            'expositor' => ['nullable', 'string', 'max:150'],
            'desarrollo' => ['required', 'string'],
            'aprendizajes' => ['required', 'string'],
            'reflexion' => ['required', 'string'],
            'autores' => ['required', 'array', 'min:1'],
            'autores.*' => ['required', 'email'],
            'portada' => ['nullable', 'string'],
            'galeria' => ['nullable', 'array', 'max:6'],
            'galeria.*' => ['required', 'string'],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'date' => 'La fecha no es válida.',
            'in' => 'El campo :attribute tiene un valor no permitido.',
            'email' => 'El correo :attribute no es válido.',
            'max' => 'El campo :attribute es demasiado largo.',
            'autores.min' => 'Agrega al menos un autor.',
        ], [
            'slug' => 'slug',
            'titulo' => 'título',
            'categoria' => 'categoría',
            'fecha' => 'fecha',
            'estado' => 'estado',
            'resumen' => 'resumen',
            'expositor' => 'expositor',
            'desarrollo' => 'desarrollo',
            'aprendizajes' => 'aprendizajes',
            'reflexion' => 'reflexión',
            'autores' => 'autores',
            'portada' => 'portada',
            'galeria' => 'galería',
        ]);

        if ($validador->fails()) {
            throw new RuntimeException(
                "{$archivo}, entrada {$numero}: ".implode(' ', $validador->errors()->all())
            );
        }

        $datos = $validador->validated();

        $categoria = $this->buscarCategoria($datos['categoria']);

        if (! $categoria) {
            $nombres = Category::orderBy('name')->pluck('name')->join(', ');
            throw new RuntimeException(
                "{$archivo}, entrada {$numero}: no existe la categoría \"{$datos['categoria']}\". Usa una de: {$nombres}."
            );
        }

        $autores = $this->buscarAutores($datos['autores'], $archivo, $numero);

        $slug = Str::slug($datos['slug'] ?? $datos['titulo']);

        if ($slug === '') {
            $slug = 'entrada';
        }

        if (in_array($slug, $this->slugs, true)) {
            throw new RuntimeException(
                "{$archivo}, entrada {$numero}: el slug \"{$slug}\" está repetido en esta importación."
            );
        }

        $this->slugs[] = $slug;

        $post = Post::where('slug', $slug)->first() ?? new Post(['slug' => $slug]);
        $estado = $this->estado($datos['estado'] ?? 'borrador');

        $post->fill([
            'category_id' => $categoria->id,
            'user_id' => $post->user_id ?? $autores->first()->id,
            'title' => $datos['titulo'],
            'activity_date' => $datos['fecha'],
            'excerpt' => $datos['resumen'] ?? null,
            'speaker' => $datos['expositor'] ?? null,
            'content' => $datos['desarrollo'],
            'learnings' => $datos['aprendizajes'],
            'reflection' => $datos['reflexion'],
            'status' => $estado,
            'published_at' => $estado === 'published'
                ? ($post->published_at ?? now())
                : null,
        ]);

        $nueva = ! $post->exists;
        $post->save();

        $post->authors()->sync($autores->pluck('id')->all());

        if (array_key_exists('portada', $datos)) {
            $this->guardarPortada($post, $datos['portada'], $carpetaBase, $archivo, $numero);
        }

        if (array_key_exists('galeria', $datos)) {
            $this->guardarGaleria($post, $datos['galeria'] ?? [], $carpetaBase, $archivo, $numero);
        }

        $this->line(sprintf(
            '  %s: %s (slug: %s)',
            $nueva ? 'creada' : 'actualizada',
            $post->title,
            $post->slug,
        ));
    }

    private function buscarCategoria(string $valor): ?Category
    {
        return Category::where('slug', Str::slug($valor))
            ->orWhereRaw('lower(name) = ?', [mb_strtolower($valor)])
            ->first();
    }

    private function buscarAutores(array $correos, string $archivo, int $numero): Collection
    {
        $correos = array_values(array_unique($correos));
        $autores = User::whereIn('email', $correos)->get();

        $faltan = array_diff($correos, $autores->pluck('email')->all());

        if ($faltan !== []) {
            throw new RuntimeException(
                "{$archivo}, entrada {$numero}: no existen estos usuarios: ".implode(', ', $faltan).'.'
            );
        }

        return $autores;
    }

    private function estado(string $valor): string
    {
        return match (mb_strtolower($valor)) {
            'publicada', 'published' => 'published',
            'borrador', 'draft' => 'draft',
            default => throw new RuntimeException("El estado \"{$valor}\" no es válido."),
        };
    }

    private function guardarPortada(
        Post $post,
        ?string $ruta,
        string $carpetaBase,
        string $archivo,
        int $numero,
    ): void {
        // Sin portada en el JSON se respeta la que ya tenga la entrada.
        // Antes se borraba, y eso eliminaba fotos subidas desde el panel.
        if ($ruta === null || $ruta === '') {
            return;
        }

        $origen = $this->imagenLocal($ruta, $carpetaBase, $archivo, $numero);
        $destino = "posts/covers/{$post->slug}.".strtolower($origen->getExtension());

        if ($post->cover_image && $post->cover_image !== $destino) {
            Storage::disk('public')->delete($post->cover_image);
        }

        Storage::disk('public')->put($destino, File::get($origen->getPathname()));
        $post->forceFill(['cover_image' => $destino])->save();
    }

    private function guardarGaleria(
        Post $post,
        array $rutas,
        string $carpetaBase,
        string $archivo,
        int $numero,
    ): void {
        $post->load('images');

        // Solo se agregan fotos nuevas. Las que ya tiene la entrada no se
        // tocan, sean del panel o de una importación anterior.
        $existentes = $post->images->keyBy('path');

        foreach (array_values($rutas) as $i => $ruta) {
            $origen = $this->imagenLocal($ruta, $carpetaBase, $archivo, $numero);
            $destino = "posts/gallery/{$post->slug}-".($i + 1).'.'.strtolower($origen->getExtension());

            if ($existentes->has($destino)) {
                continue;
            }

            Storage::disk('public')->put($destino, File::get($origen->getPathname()));

            $post->images()->create([
                'path' => $destino,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function imagenLocal(
        string $ruta,
        string $carpetaBase,
        string $archivo,
        int $numero,
    ): SplFileInfo {
        $ruta = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta);
        $completa = $this->rutaAbsoluta($ruta, $carpetaBase);

        if (! File::isFile($completa)) {
            throw new RuntimeException(
                "{$archivo}, entrada {$numero}: no existe la imagen \"{$ruta}\"."
            );
        }

        $info = new SplFileInfo($completa);
        $extension = strtolower($info->getExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new RuntimeException(
                "{$archivo}, entrada {$numero}: la imagen \"{$ruta}\" debe ser JPG, PNG o WebP."
            );
        }

        if ($info->getSize() > 4 * 1024 * 1024) {
            throw new RuntimeException(
                "{$archivo}, entrada {$numero}: la imagen \"{$ruta}\" supera los 4 MB."
            );
        }

        return $info;
    }

    private function rutaArchivo(string $ruta, string $base): string
    {
        $ruta = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta);

        if (Str::startsWith($ruta, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $ruta) === 1) {
            return $ruta;
        }

        $desdeProyecto = base_path($ruta);

        if (File::isFile($desdeProyecto)) {
            return $desdeProyecto;
        }

        return $base.DIRECTORY_SEPARATOR.$ruta;
    }

    private function rutaAbsoluta(string $ruta, string $base): string
    {
        if (Str::startsWith($ruta, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $ruta) === 1) {
            return $ruta;
        }

        return $base.DIRECTORY_SEPARATOR.$ruta;
    }
}
