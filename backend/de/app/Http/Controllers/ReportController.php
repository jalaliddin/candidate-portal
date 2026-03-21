<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function candidateList(Request $request): JsonResponse
    {
        $query = Candidate::query();

        if ($request->user()->role === 'admin') {
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
        } else {
            $query->where('status', 'Active');
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('occupation', 'LIKE', "%{$search}%");
            });
        }

        if ($occupations = $request->input('occupation')) {
            $query->whereIn('occupation', (array) $occupations);
        }

        if ($levels = $request->input('german_level')) {
            $query->whereIn('german_level', (array) $levels);
        }

        if ($nationalities = $request->input('nationality')) {
            $query->whereIn('nationality', (array) $nationalities);
        }

        if ($countries = $request->input('country_of_origin')) {
            $query->whereIn('country_of_origin', (array) $countries);
        }

        return response()->json($query->orderBy('id')->limit(500)->get());
    }

    public function requestsStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $baseQuery = CandidateRequest::query();

        if ($user->role !== 'admin') {
            $baseQuery->where('user_id', $user->id);
        }

        $total = (clone $baseQuery)->count();

        $byType = [
            'reserve' => (clone $baseQuery)->where('request_type', 'reserve')->count(),
            'more_info' => (clone $baseQuery)->where('request_type', 'more_info')->count(),
            'interview' => (clone $baseQuery)->where('request_type', 'interview')->count(),
        ];

        $byStatus = [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'done' => (clone $baseQuery)->where('status', 'done')->count(),
        ];

        $stats = [
            'total_requests' => $total,
            'by_type' => $byType,
            'by_status' => $byStatus,
        ];

        if ($user->role === 'admin') {
            $stats['by_month'] = CandidateRequest::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(fn ($row) => ['month' => $row->month, 'count' => $row->count]);
        }

        $topQuery = CandidateRequest::query()
            ->selectRaw('candidate_id, COUNT(*) as total_count,
                SUM(CASE WHEN request_type = "reserve" THEN 1 ELSE 0 END) as reserve_count,
                SUM(CASE WHEN request_type = "more_info" THEN 1 ELSE 0 END) as more_info_count,
                SUM(CASE WHEN request_type = "interview" THEN 1 ELSE 0 END) as interview_count');

        if ($user->role !== 'admin') {
            $topQuery->where('user_id', $user->id);
        }

        $stats['top_requested_candidates'] = $topQuery
            ->groupBy('candidate_id')
            ->orderByDesc('total_count')
            ->limit(5)
            ->with('candidate:id,first_name,last_name,occupation')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->candidate_id,
                'first_name' => $row->candidate?->first_name,
                'last_name' => $row->candidate?->last_name,
                'occupation' => $row->candidate?->occupation,
                'reserve' => $row->reserve_count,
                'more_info' => $row->more_info_count,
                'interview' => $row->interview_count,
                'count' => $row->total_count,
            ]);

        return response()->json($stats);
    }

    public function candidateProfile(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $candidate = Candidate::findOrFail($id);

        $requestQuery = CandidateRequest::query()->where('candidate_id', $id);

        if ($user->role !== 'admin') {
            $requestQuery->where('user_id', $user->id);
            $requests = $requestQuery->orderByDesc('created_at')->get();
        } else {
            $requests = $requestQuery
                ->with('user:id,name,email,company_name')
                ->orderByDesc('created_at')
                ->get();
        }

        return response()->json([
            'candidate' => $candidate,
            'requests' => $requests,
        ]);
    }
}
