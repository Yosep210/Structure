<?php

namespace App\Concerns\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait Filters
{
    /**
     * @param  Builder<Model>  $query
     * @param  array{
     *     text?: array<string, string>,
     *     select?: array<string, string>,
     *     multi?: array<string, array<string>>,
     *     nominal_range?: array<string, string>,
     *     total_range?: array<string, string>,
     *     date?: array<string, string>
     * }  $config
     * @return Builder<Model>
     */
    protected function filters(Builder $query, Request $request, array $config): Builder
    {
        // 1. Text Search single-Column input (LIKE %term%)
        if (! empty($config['text'])) {
            foreach ($config['text'] as $filterKey => $column) {
                if ($request->filled($filterKey)) {
                    $term = $request->string($filterKey)->trim()->value();
                    $query->whereLike($column, '%'.$term.'%');
                }
            }
        }

        // 2. Text Search Multi-Column input (OR LIKE)
        if (! empty($config['multi'])) {
            foreach ($config['multi'] as $filterKey => $columns) {
                if ($request->filled($filterKey)) {
                    $searchTerm = trim((string) $request->input($filterKey));
                    $query->where(function (Builder $subQuery) use ($columns, $searchTerm) {
                        foreach ($columns as $index => $col) {
                            if ($index === 0) {
                                $subQuery->where($col, 'like', '%'.$searchTerm.'%');
                            } else {
                                $subQuery->orWhere($col, 'like', '%'.$searchTerm.'%');
                            }
                        }
                    });
                }
            }
        }

        // 3. Select Match (WHERE col = val)
        if (! empty($config['select'])) {
            foreach ($config['select'] as $filterKey => $column) {
                if ($request->filled($filterKey)) {
                    $query->where($column, $request->input($filterKey));
                }
            }
        }

        // 4. Range (_min & _max) for nominal
        if (! empty($config['nominal_range'])) {
            foreach ($config['nominal_range'] as $filterKey => $column) {
                $minKey = $filterKey.'_min';
                $maxKey = $filterKey.'_max';

                if ($request->filled($minKey) && is_numeric($request->input($minKey))) {
                    $query->where($column, '>=', (int) $request->input($minKey));
                }
                if ($request->filled($maxKey) && is_numeric($request->input($maxKey))) {
                    $query->where($column, '<=', (int) $request->input($maxKey));
                }
            }
        }

        // 5. Aggregate / Having Range (_min & _max HAVING) for SUM()/COUNT() or total
        if (! empty($config['total_range'])) {
            foreach ($config['total_range'] as $filterKey => $columnAlias) {
                $minKey = $filterKey.'_min';
                $maxKey = $filterKey.'_max';

                if ($request->filled($minKey) && is_numeric($request->input($minKey))) {
                    $query->havingRaw("{$columnAlias} >= ?", [(int) $request->input($minKey)]);
                }

                if ($request->filled($maxKey) && is_numeric($request->input($maxKey))) {
                    $query->havingRaw("{$columnAlias} <= ?", [(int) $request->input($maxKey)]);
                }
            }
        }

        // 6. Date Range (_from & _to)
        if (! empty($config['date'])) {
            foreach ($config['date'] as $filterKey => $column) {
                $fromKey = $filterKey.'_from';
                $toKey = $filterKey.'_to';

                if ($request->filled($fromKey)) {
                    $query->whereDate($column, '>=', $request->input($fromKey));
                }
                if ($request->filled($toKey)) {
                    $query->whereDate($column, '<=', $request->input($toKey));
                }
            }
        }

        return $query;
    }
}
