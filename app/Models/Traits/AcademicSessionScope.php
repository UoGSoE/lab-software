<?php

namespace App\Models\Traits;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Builder;

trait AcademicSessionScope
{
    public function scopeForAcademicSession(Builder $query, AcademicSession $academicSession): Builder
    {
        return $query->where('academic_session_id', '=', $academicSession->id);
    }
}
