<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'status', 'image_path', 'first_name', 'last_name', 'nationality',
    'date_of_birth', 'place_of_birth', 'german_level', 'certificate',
    'expose_university_degree', 'anabin_status', 'additional_languages',
    'occupation', 'country_of_origin',
])]
class Candidate extends Model
{
    /** @use HasFactory<\Database\Factories\CandidateFactory> */
    use HasFactory;

    protected $appends = ['age'];

    protected function casts(): array
    {
        return [
            'additional_languages' => 'array',
            'date_of_birth' => 'date',
        ];
    }

    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    public function candidateRequests(): HasMany
    {
        return $this->hasMany(CandidateRequest::class);
    }
}
