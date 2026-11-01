<?php

namespace App\Concerns\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Sorts
{
    /**
     * Apply sorting to the query builder.
     *
     * @param  array<string, string>  $sortColumns
     * @param  string|array<string, string>  $defaultSort
     * @param  string|array<string, string|null>|null  $tieBreaker
     * @return array{sort: string, direction: string}
     */
    protected function sort(
        Builder $query,
        Request $request,
        array $sortColumns,
        string|array $defaultSort = 'created_at',
        string $defaultDirection = 'desc',
        string|array|null $tieBreaker = 'id'
    ): array {
        // 1. Custom client sort (User clicked a table column header)
        $hasCustomSort = in_array($request->input('sort'), array_keys($sortColumns), true);
        $normalizeDirection = static function (mixed $value, string $fallback): string {
            $value = strtolower((string) $value);

            return in_array($value, ['asc', 'desc'], true) ? $value : $fallback;
        };
        $defaultDirection = $normalizeDirection($defaultDirection, 'desc');

        if ($hasCustomSort) {
            $sort = (string) $request->input('sort');
            $direction = $normalizeDirection(
                $request->input('direction'),
                $defaultDirection,
            );

            $query->orderBy($sortColumns[$sort], $direction);
        } elseif (is_array($defaultSort)) {
            // 2. Multi-column default sort (Hierarchical ordering)
            $primarySort = null;
            $primaryDirection = $defaultDirection;

            foreach ($defaultSort as $col => $dir) {
                $column = $sortColumns[$col] ?? $col;
                $directionValue = $normalizeDirection($dir, 'asc');

                $query->orderBy($column, $directionValue);

                if ($primarySort === null) {
                    $primarySort = $col;
                    $primaryDirection = $directionValue;
                }
            }

            $sort = $primarySort ?? 'created_at';
            $direction = $primaryDirection;
        } else {
            // 3. Single-column default sort (Standard fallback ordering)
            $sort = $defaultSort;
            $direction = $normalizeDirection(
                $request->input('direction'),
                $defaultDirection,
            );

            $query->orderBy(
                $sortColumns[$sort] ?? $sortColumns[$defaultSort] ?? $defaultSort,
                $direction,
            );
        }

        // 4. Deterministic tie-breaker sort (Prevents data shifting across pagination pages)
        if ($tieBreaker !== null) {
            if (is_array($tieBreaker)) {
                foreach ($tieBreaker as $column => $tieBreakerDirection) {
                    $tieBreakerDirection = $normalizeDirection($tieBreakerDirection, 'asc');

                    $query->orderBy(
                        $sortColumns[$column] ?? $column,
                        $tieBreakerDirection,
                    );
                }
            } else {
                $tieBreakerColumn = ($tieBreaker === 'id' && $query->getModel())
                    ? $query->getModel()->getQualifiedKeyName()
                    : ($sortColumns[$tieBreaker] ?? $tieBreaker);

                $query->orderBy($tieBreakerColumn);
            }
        }

        return [
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
