<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait Sortable
{
    protected function applySorting($query, array $sortableColumns, string $defaultSort = 'created_at', string $defaultDirection = 'desc')
    {
        $request = request();
        $sort = $request->input('sort', $defaultSort);
        $direction = $request->input('direction', $defaultDirection);

        if (!in_array($sort, $sortableColumns)) {
            $sort = $defaultSort;
        }

        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = $defaultDirection;
        }

        return $query->orderBy($sort, $direction);
    }
}
