<?php

namespace App\Http\Controllers;

use App\Models\CandidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CandidateRequest::query()
            ->with(['candidate', 'user'])
            ->where('user_id', $request->user()->id);

        $this->applyFilters($query, $request);

        return response()->json($query->orderByDesc('created_at')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'request_type' => 'required|in:reserve,more_info,interview',
            'message' => 'nullable|string|max:2000',
        ]);

        $duplicate = CandidateRequest::query()
            ->where('user_id', $request->user()->id)
            ->where('candidate_id', $request->candidate_id)
            ->where('request_type', $request->request_type)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'You already have an active request of this type for this candidate.',
            ], 422);
        }

        $candidateRequest = CandidateRequest::create([
            'candidate_id' => $request->candidate_id,
            'user_id' => $request->user()->id,
            'request_type' => $request->request_type,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json($candidateRequest->load(['candidate', 'user']), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $candidateRequest = CandidateRequest::findOrFail($id);

        $request->validate([
            'status' => 'nullable|in:pending,processing,done',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $candidateRequest->update($request->only(['status', 'admin_note']));

        return response()->json($candidateRequest->load(['candidate', 'user']));
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $query = CandidateRequest::query()
            ->with(['candidate', 'user'])
            ->orderByDesc('created_at');

        $this->applyFilters($query, $request);

        return response()->json($query->paginate(20));
    }

    private function applyFilters($query, Request $request): void
    {
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('request_type')) {
            $query->where('request_type', $type);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
    }
}
