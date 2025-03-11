<?php

namespace App\Models\Scopes;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class AcademicSessionScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (session()->missing('academic_session_id')) {
            session()->put('academic_session_id', AcademicSession::getUsersSession()->id);
        }
        $builder->where('academic_session_id', '=', session('academic_session_id'));
    }
}
