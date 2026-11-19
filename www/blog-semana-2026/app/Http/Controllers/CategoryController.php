<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('posts')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Category::create([
            ...$data,
            'slug' => $this->uniqueSlug($data['name']),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Categoría creada correctamente.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'No se puede eliminar una categoría que tiene entradas.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('status', 'Categoría eliminada correctamente.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);

        // Si el nombre no deja texto, usamos un slug fijo.
        if ($base === '') {
            $base = 'categoria';
        }

        $slug = $base;
        $contador = 2;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$contador;
            $contador++;
        }

        return $slug;
    }
}
