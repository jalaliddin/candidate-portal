<?php

namespace App\Http\Controllers;

use App\Models\Occupation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOccupationController extends Controller
{
    /** Active occupations list for select dropdowns */
    public function list(): JsonResponse
    {
        return response()->json(
            Occupation::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function index(Request $request): JsonResponse
    {
        $query = Occupation::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        return response()->json($query->orderBy('sort_order')->orderBy('name')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:occupations,name',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        return response()->json(Occupation::create($request->only(['name', 'is_active', 'sort_order'])), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $occupation = Occupation::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:occupations,name,'.$id,
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $occupation->update($request->only(['name', 'is_active', 'sort_order']));

        return response()->json($occupation);
    }

    public function destroy(int $id): JsonResponse
    {
        Occupation::findOrFail($id)->delete();

        return response()->json(['message' => 'Occupation deleted.']);
    }
}
