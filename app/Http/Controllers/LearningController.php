<?php

namespace App\Http\Controllers;

use App\Models\LearningResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningController extends Controller
{
    public function index(Request $request): Response
    {
        $resources = LearningResource::query()
            ->with('skills')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search'));

                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('provider', 'like', "%{$search}%");
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('resource_type', $request->string('type'));
            })
            ->orderBy('title')
            ->paginate(9)
            ->withQueryString();

        $types = LearningResource::query()
            ->distinct()
            ->pluck('resource_type')
            ->values()
            ->all();

        return Inertia::render('Learning/Index', [
            'resources' => $resources,
            'types' => $types,
            'filters' => [
                'search' => $request->string('search')->toString() ?? '',
                'type' => $request->string('type')->toString() ?? '',
            ],
        ]);
    }
}