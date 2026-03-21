<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'user_id', 'request_type', 'message', 'status', 'admin_note'])]
class CandidateRequest extends Model
{
    /** @use HasFactory<\Database\Factories\CandidateRequestFactory> */
    use HasFactory;

    protected $table = 'candidate_requests';

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
