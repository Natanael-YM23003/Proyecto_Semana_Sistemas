<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function home(): View
    {
        $latest = Post::published()
            ->with(['category', 'authors'])
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $categories = Category::withCount([
            'posts' => fn ($query) => $query->published(),
        ])->orderBy('name')->get();

        $total = Post::published()->count();

        return view('blog.home', compact('latest', 'categories', 'total'));
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('buscar', ''));
        $categorySlug = trim((string) $request->query('categoria', ''));

        $categories = Category::withCount([
            'posts' => fn ($query) => $query->published(),
        ])->orderBy('name')->get();

        $category = $categorySlug !== ''
            ? Category::where('slug', $categorySlug)->firstOrFail()
            : null;

        $posts = Post::published()
            ->with(['category', 'authors'])
            ->when($search !== '', function ($query) use ($search) {
                $texto = '%'.mb_strtolower($search).'%';

                $query->where(function ($query) use ($texto) {
                    $query->whereRaw('lower(title) like ?', [$texto])
                        ->orWhereRaw('lower(excerpt) like ?', [$texto])
                        ->orWhereRaw('lower(content) like ?', [$texto])
                        ->orWhereRaw('lower(learnings) like ?', [$texto])
                        ->orWhereRaw('lower(reflection) like ?', [$texto])
                        ->orWhereRaw('lower(speaker) like ?', [$texto]);
                });
            })
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', compact('posts', 'categories', 'category', 'search'));
    }

    public function show(Post $post): View
    {
        abort_unless($post->status === 'published', 404);

        $post->load(['category', 'authors', 'images', 'evidences']);

        $related = Post::published()
            ->with(['category', 'authors'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
