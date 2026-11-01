<?php

namespace App\Concerns\Table;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Paginates
{
    private const ALLOWED_PER_PAGE = [10, 25, 50, 100];

    /**
     * Paginate the given query based on request parameters.
     */
    protected function paginate(Builder $query, Request $request, int $defaultPerPage = 10): LengthAwarePaginator
    {
        // 1. Handle "all" records request (Clones query count to preserve LengthAwarePaginator structure)
        if ($request->input('per_page') === 'all') {
            $total = (clone $query)->count();

            return $query->paginate(max($total, 1), ['*'], 'page', 1, $total)->withQueryString();
        }

        // 2. Validate per_page input against allowed whitelist
        $perPage = (int) $request->input('per_page', $defaultPerPage);
        $allowedPerPage = self::ALLOWED_PER_PAGE;

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = $defaultPerPage;
        }

        // 3. Resolve current page number
        $page = max(1, (int) $request->input('page', 1));

        // 4. Execute pagination with preserved query string parameters for links
        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    /**
     * Build the pagination state payload for Inertia view props.
     *
     * @param  array<int, string>  $filterKeys
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getPaginatePayload(Request $request, array $filterKeys, array $data, int $defaultPerPage = 10): array
    {
        $payload = [];

        // 1. Extract filter inputs preserving strings and arrays
        foreach ($filterKeys as $key) {
            $value = $request->input($key, '');
            $payload[$key] = is_array($value) ? $value : (string) $value;
        }

        // 2. Attach sort state with safe defaults
        $payload['sort'] = $data['sort'] ?? 'id';
        $payload['direction'] = $data['direction'] ?? 'desc';

        // 3. Attach per_page value matching the allowed pagination whitelist
        $perPage = $request->input('per_page');
        if ($perPage === 'all') {
            $payload['per_page'] = 'all';
        } else {
            $perPageInt = (int) $perPage;
            $payload['per_page'] = in_array($perPageInt, self::ALLOWED_PER_PAGE, true) ? $perPageInt : $defaultPerPage;
        }

        return $payload;
    }
}
