<?php

namespace App\Models\Concerns;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

trait ScopedByAgence
{
    protected function scopeByAgence(Builder $query, string $column = 'agence_id'): Builder
    {
        $user = auth()->user();
        if (!$user || $user->scope === 'dg') {
            return $query;
        }

        $agenceId = Employee::where('user_id', $user->id)->value('agence_id');
        if ($agenceId) {
            $query->where($column, $agenceId);
        }

        return $query;
    }

    protected function scopeByAgenceRelation(Builder $query, string $relation, string $column = 'agence_id'): Builder
    {
        $user = auth()->user();
        if (!$user || $user->scope === 'dg') {
            return $query;
        }

        $agenceId = Employee::where('user_id', $user->id)->value('agence_id');
        if ($agenceId) {
            $query->whereHas($relation, fn($q) => $q->where($column, $agenceId));
        }

        return $query;
    }
}
