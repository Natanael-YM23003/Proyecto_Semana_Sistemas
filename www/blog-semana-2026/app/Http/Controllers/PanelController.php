<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Evidence;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderCrud($request);
    }

    public function crud(Request $request): View
    {
        return $this->renderCrud($request);
    }

    private function renderCrud(Request $request): View
    {
        $tipo = $request->query('tipo', 'todos');
        $busqueda = trim((string) $request->query('q', ''));

        $counts = [
            'posts' => Post::count(),
            'published' => Post::published()->count(),
            'drafts' => Post::draft()->count(),
            'categories' => Category::count(),
            'evidences' => Evidence::count(),
        ];

        $postsQuery = Post::with(['category', 'authors', 'creator'])
            ->orderByDesc('activity_date')
            ->orderByDesc('id');

        $evidencesQuery = Evidence::with(['user', 'post'])
            ->orderByDesc('taken_at')
            ->orderByDesc('id');

        if ($busqueda !== '') {
            $postsQuery->where(function ($q) use ($busqueda) {
                $q->where('title', 'like', "%{$busqueda}%")
                  ->orWhere('excerpt', 'like', "%{$busqueda}%")
                  ->orWhere('speaker', 'like', "%{$busqueda}%");
            });

            $evidencesQuery->where(function ($q) use ($busqueda) {
                $q->where('title', 'like', "%{$busqueda}%")
                  ->orWhere('event_reference', 'like', "%{$busqueda}%")
                  ->orWhere('description', 'like', "%{$busqueda}%");
            });
        }

        $posts = $postsQuery->get();
        $evidences = $evidencesQuery->get();

        $combined = collect();

        foreach ($posts as $post) {
            $combined->push((object) [
                'type' => 'post',
                'type_label' => 'Entrada',
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title,
                'reference' => $post->category?->name ?? 'Sin categoría',
                'date' => $post->activity_date,
                'author' => $post->authors->isNotEmpty()
                    ? $post->authors->pluck('name')->join(', ')
                    : ($post->creator?->name ?? 'Equipo'),
                'image_url' => $post->coverUrl(),
                'status' => $post->status,
                'edit_url' => route('posts.edit', $post),
                'delete_url' => route('posts.destroy', $post),
                'view_url' => route('blog.show', $post),
                'model' => $post,
            ]);
        }

        foreach ($evidences as $evidence) {
            $combined->push((object) [
                'type' => 'evidence',
                'type_label' => 'Evidencia',
                'id' => $evidence->id,
                'slug' => null,
                'title' => $evidence->title,
                'reference' => $evidence->event_reference,
                'date' => $evidence->taken_at ?? $evidence->created_at,
                'author' => $evidence->user?->name ?? 'Equipo',
                'image_url' => $evidence->imageUrl(),
                'status' => 'evidence',
                'edit_url' => route('evidences.edit', $evidence),
                'delete_url' => route('evidences.destroy', $evidence),
                'view_url' => $evidence->imageUrl(),
                'model' => $evidence,
            ]);
        }

        $combined = $combined->sortByDesc(fn ($item) => $item->date ? $item->date->timestamp : 0)->values();

        return view('panel.index', [
            'counts' => $counts,
            'posts' => $posts,
            'evidences' => $evidences,
            'combined' => $combined,
            'currentTipo' => $tipo,
            'busqueda' => $busqueda,
        ]);
    }
}
