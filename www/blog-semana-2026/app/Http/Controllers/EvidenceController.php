<?php

namespace App\Http\Controllers;

use App\Http\Requests\EvidenceRequest;
use App\Models\Evidence;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EvidenceController extends Controller
{
    public function index(Request $request): View
    {
        $selectedEvent = $request->query('evento');

        $query = Evidence::with(['user', 'post'])
            ->orderByDesc('taken_at')
            ->orderByDesc('id');

        if ($selectedEvent) {
            $query->where('event_reference', $selectedEvent);
        }

        $evidences = $query->paginate(12)->withQueryString();

        $eventCounts = Evidence::selectRaw('event_reference, count(*) as count')
            ->whereNotNull('event_reference')
            ->where('event_reference', '!=', '')
            ->groupBy('event_reference')
            ->pluck('count', 'event_reference');

        return view('evidences.index', [
            'evidences' => $evidences,
            'selectedEvent' => $selectedEvent,
            'eventCounts' => $eventCounts,
            'predefinedEvents' => Evidence::EVENT_REFERENCES,
        ]);
    }

    public function create(): View
    {
        return view('evidences.create', [
            'posts' => Post::orderByDesc('activity_date')->get(),
            'predefinedEvents' => Evidence::EVENT_REFERENCES,
        ]);
    }

    public function store(EvidenceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $path = $request->file('image')->store('evidences', 'public');

        Evidence::create([
            'title' => $data['title'],
            'event_reference' => $data['event_reference'],
            'path' => $path,
            'post_id' => $data['post_id'] ?? null,
            'user_id' => $request->user()?->id,
            'description' => $data['description'] ?? null,
            'taken_at' => $data['taken_at'] ?? now()->toDateString(),
        ]);

        return redirect()
            ->route('evidences.index')
            ->with('status', 'Evidencia agregada exitosamente.');
    }

    public function edit(Evidence $evidence): View
    {
        return view('evidences.edit', [
            'evidence' => $evidence,
            'posts' => Post::orderByDesc('activity_date')->get(),
            'predefinedEvents' => Evidence::EVENT_REFERENCES,
        ]);
    }

    public function update(EvidenceRequest $request, Evidence $evidence): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteFile($evidence->path);
            $evidence->path = $request->file('image')->store('evidences', 'public');
        }

        $evidence->title = $data['title'];
        $evidence->event_reference = $data['event_reference'];
        $evidence->post_id = $data['post_id'] ?? null;
        $evidence->description = $data['description'] ?? null;
        if (! empty($data['taken_at'])) {
            $evidence->taken_at = $data['taken_at'];
        }

        $evidence->save();

        return redirect()
            ->route('evidences.index')
            ->with('status', 'Evidencia actualizada exitosamente.');
    }

    public function destroy(Evidence $evidence): RedirectResponse
    {
        $this->deleteFile($evidence->path);
        $evidence->delete();

        return redirect()
            ->back()
            ->with('status', 'Evidencia eliminada exitosamente.');
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
