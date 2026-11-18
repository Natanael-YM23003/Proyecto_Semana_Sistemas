<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        // Totales para las tarjetas del panel.
        $counts = [
            'posts' => Post::count(),
            'published' => Post::published()->count(),
            'drafts' => Post::draft()->count(),
            'categories' => Category::count(),
        ];

        $latest = Post::with('category')
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('panel.index', compact('counts', 'latest'));
    }
}
