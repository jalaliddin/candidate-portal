<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'active_candidates' => Candidate::query()->where('status', 'Active')->count(),
            'inactive_candidates' => Candidate::query()->where('status', 'Inactive')->count(),
            'placed_candidates' => Candidate::query()->where('status', 'Placed')->count(),
            'total_clients' => User::query()->where('role', 'client')->count(),
            'active_clients' => User::query()->where('role', 'client')->where('is_active', true)->count(),
            'pending_requests' => CandidateRequest::query()->where('status', 'pending')->count(),
            'processing_requests' => CandidateRequest::query()->where('status', 'processing')->count(),
            'done_requests' => CandidateRequest::query()->where('status', 'done')->count(),
            'new_requests_this_week' => CandidateRequest::query()
                ->where('status', 'done')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'requests_by_type' => [
                'reserve' => CandidateRequest::query()->where('request_type', 'reserve')->count(),
                'more_info' => CandidateRequest::query()->where('request_type', 'more_info')->count(),
                'interview' => CandidateRequest::query()->where('request_type', 'interview')->count(),
            ],
            'top_nationalities' => Candidate::query()
                ->selectRaw('nationality, COUNT(*) as count')
                ->groupBy('nationality')
                ->orderByDesc('count')
                ->limit(5)
                ->get(['nationality', 'count']),
            'top_occupations' => Candidate::query()
                ->selectRaw('occupation, COUNT(*) as count')
                ->groupBy('occupation')
                ->orderByDesc('count')
                ->limit(5)
                ->get(['occupation', 'count']),
        ]);
    }

    public function candidateIndex(Request $request): JsonResponse
    {
        $query = Candidate::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('occupation', 'LIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($occupations = $request->input('occupation')) {
            $query->whereIn('occupation', (array) $occupations);
        }

        if ($levels = $request->input('german_level')) {
            $query->whereIn('german_level', (array) $levels);
        }

        return response()->json($query->orderByDesc('id')->paginate(20));
    }

    public function candidateStore(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'occupation' => 'required|string|max:255',
            'country_of_origin' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive,Placed',
            'german_level' => 'required|in:None,A1,A2,B1,B2,C1,C2,Native',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'additional_languages' => 'nullable|string',
        ]);

        $data = $request->except(['image', 'additional_languages']);

        if ($request->hasFile('image')) {
            $filename = Str::uuid().'.'.$request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('candidates', $filename, 'public');
            $data['image_path'] = $filename;
        }

        if ($request->input('additional_languages')) {
            $data['additional_languages'] = json_decode($request->input('additional_languages'), true);
        }

        return response()->json(Candidate::create($data), 201);
    }

    public function candidateUpdate(Request $request, int $id): JsonResponse
    {
        $candidate = Candidate::findOrFail($id);

        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'nationality' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'sometimes|required|date',
            'occupation' => 'sometimes|required|string|max:255',
            'country_of_origin' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:Active,Inactive,Placed',
            'german_level' => 'sometimes|required|in:None,A1,A2,B1,B2,C1,C2,Native',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'additional_languages' => 'nullable|string',
        ]);

        $data = $request->except(['image', 'additional_languages']);

        if ($request->hasFile('image')) {
            if ($candidate->image_path) {
                Storage::disk('public')->delete('candidates/'.$candidate->image_path);
            }
            $filename = Str::uuid().'.'.$request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('candidates', $filename, 'public');
            $data['image_path'] = $filename;
        }

        if ($request->input('additional_languages') !== null) {
            $data['additional_languages'] = json_decode($request->input('additional_languages'), true);
        }

        $candidate->update($data);

        return response()->json($candidate->fresh());
    }

    public function candidateDestroy(int $id): JsonResponse
    {
        Candidate::findOrFail($id)->update(['status' => 'Inactive']);

        return response()->json(['message' => 'Candidate deactivated.']);
    }

    public function userIndex(Request $request): JsonResponse
    {
        $query = User::query()->where('role', 'client');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('company_name', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->withCount('candidateRequests')->orderByDesc('id')->paginate(20));
    }

    public function userStore(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'company_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => 'client',
            'company_name' => $request->company_name,
            'country' => $request->country,
            'is_active' => true,
            'preferred_language' => 'en',
        ]);

        return response()->json([
            'user' => $user,
            'generated_password' => $password,
        ], 201);
    }

    public function userUpdate(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$id,
            'company_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($request->only(['name', 'email', 'company_name', 'country', 'is_active']));

        return response()->json($user);
    }

    public function userDestroy(int $id): JsonResponse
    {
        User::findOrFail($id)->update(['is_active' => false]);

        return response()->json(['message' => 'User deactivated.']);
    }
}
