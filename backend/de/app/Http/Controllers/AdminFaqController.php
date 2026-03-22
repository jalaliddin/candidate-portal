<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFaqController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Faq::query()->orderBy('sort_order')->orderBy('id')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question'   => 'required|string|max:500',
            'answer'     => 'required|string',
            'category'   => 'nullable|string|max:100',
            'is_active'  => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        return response()->json(Faq::create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);

        $data = $request->validate([
            'question'   => 'required|string|max:500',
            'answer'     => 'required|string',
            'category'   => 'nullable|string|max:100',
            'is_active'  => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $faq->update($data);

        return response()->json($faq);
    }

    public function destroy(int $id): JsonResponse
    {
        Faq::findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
