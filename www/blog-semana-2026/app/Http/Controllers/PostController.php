<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::with(['category', 'authors'])
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('posts.create', [
            'categories' => Category::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['authors', 'images', 'cover_image', 'remove_images']);
        $authors = array_map('intval', (array) $request->input('authors', []));

        $post = Post::create([
            ...$data,
            'user_id' => $request->user()->id,
            'slug' => $this->uniqueSlug($data['title']),
            'published_at' => $data['status'] === 'published' ? now() : null,
            'cover_image' => $this->storeFile($request->file('cover_image'), 'posts/covers'),
        ]);

        $post->authors()->sync($authors);
        $this->storeGallery($post, (array) $request->file('images', []));

        return redirect()
            ->route('posts.index')
            ->with('status', 'Entrada creada correctamente.');
    }

    public function edit(Post $post): View
    {
        $post->load(['authors', 'images']);

        return view('posts.edit', [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->safe()->except(['authors', 'images', 'cover_image', 'remove_images']);
        $authors = array_map('intval', (array) $request->input('authors', []));

        // Se guarda la fecha la primera vez que se publica.
        if ($data['status'] === 'published' && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteFile($post->cover_image);
            $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'posts/covers');
        }

        $post->update($data);
        $post->authors()->sync($authors);

        $this->removeImages($post, (array) $request->input('remove_images', []));
        $this->storeGallery($post, (array) $request->file('images', []));

        return redirect()
            ->route('posts.index')
            ->with('status', 'Entrada actualizada correctamente.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->load('images');

        $this->deleteFile($post->cover_image);

        foreach ($post->images as $image) {
            $this->deleteFile($image->path);
        }

        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('status', 'Entrada eliminada correctamente.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        // Si el título no deja texto, usamos un slug fijo.
        if ($base === '') {
            $base = 'entrada';
        }

        $slug = $base;
        $contador = 2;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$contador;
            $contador++;
        }

        return $slug;
    }

    private function storeFile(?UploadedFile $file, string $folder): ?string
    {
        return $file?->store($folder, 'public');
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    // Sigue el orden de las imágenes que ya existen.
    private function storeGallery(Post $post, array $images): void
    {
        $orden = (int) $post->images()->reorder()->max('sort_order');

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            $post->images()->create([
                'path' => $image->store('posts/gallery', 'public'),
                'sort_order' => ++$orden,
            ]);
        }
    }

    private function removeImages(Post $post, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        foreach ($post->images()->whereIn('id', $ids)->get() as $image) {
            $this->deleteFile($image->path);
            $image->delete();
        }
    }
}
