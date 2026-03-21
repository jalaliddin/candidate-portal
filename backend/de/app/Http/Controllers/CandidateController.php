<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Candidate::query()->where('status', 'Active');

        $this->applyFilters($query, $request);

        return response()->json($query->orderBy('id')->paginate(12));
    }

    public function filterOptions(): JsonResponse
    {
        $germanLevelOrder = ['None', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Native'];

        $occupations = Candidate::query()->where('status', 'Active')
            ->distinct()->orderBy('occupation')->pluck('occupation');

        $nationalities = Candidate::query()->where('status', 'Active')
            ->distinct()->orderBy('nationality')->pluck('nationality');

        $levels = Candidate::query()->where('status', 'Active')
            ->distinct()->pluck('german_level');

        $sortedLevels = collect($germanLevelOrder)
            ->filter(fn ($l) => $levels->contains($l))
            ->values();

        $countries = Candidate::query()->where('status', 'Active')
            ->distinct()->orderBy('country_of_origin')->pluck('country_of_origin');

        return response()->json([
            'occupations' => $occupations,
            'nationalities' => $nationalities,
            'german_levels' => $sortedLevels,
            'countries_of_origin' => $countries,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Candidate::findOrFail($id));
    }

    protected function applyFilters($query, Request $request): void
    {
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

        if ($lang = $request->input('additional_language')) {
            $query->where('additional_languages', 'LIKE', "%{$lang}%");
        }

        if ($ageMin = $request->input('age_min')) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [(int) $ageMin]);
        }

        if ($ageMax = $request->input('age_max')) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?', [(int) $ageMax]);
        }
    }
}
