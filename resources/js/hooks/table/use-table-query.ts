import type { PaginatedData } from '@/components/table/paginates/pagination';
import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';

export type UseTableQueryOptions<T, F extends Record<string, string>> = {
    endpoint: string;
    data?: T[] | PaginatedData<T>;
    filters?: Partial<F> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
    defaultSortKey: string;
    defaultSortDirection?: 'asc' | 'desc';
    defaultPerPage?: number | 'all';
    defaultFilters: F;
    only?: string[];
    debounceMs?: number;
    cleanUrlOnReset?: boolean;
};

export function useTableQuery<T, F extends Record<string, string>>({
    endpoint,
    data,
    filters,
    defaultSortKey,
    defaultSortDirection = 'desc',
    defaultPerPage = 10,
    defaultFilters,
    only,
    debounceMs = 300,
    cleanUrlOnReset = false,
}: UseTableQueryOptions<T, F>) {
    const isPaginated = Boolean(
        data && typeof data === 'object' && !Array.isArray(data),
    );
    const paginatedData = isPaginated ? (data as PaginatedData<T>) : undefined;
    const rows: T[] = Array.isArray(data) ? data : (paginatedData?.data ?? []);

    const total = isPaginated
        ? (paginatedData?.total ?? rows.length)
        : rows.length;
    const currentPage = isPaginated ? (paginatedData?.current_page ?? 1) : 1;
    const lastPage = isPaginated ? (paginatedData?.last_page ?? 1) : 1;

    const [perPage, setPerPage] = useState<number | 'all'>(
        filters?.per_page ?? defaultPerPage,
    );
    const [sortKey, setSortKey] = useState<string>(
        filters?.sort ?? defaultSortKey,
    );
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>(
        filters?.direction ?? defaultSortDirection,
    );
    const [columnFilters, setColumnFilters] = useState<F>(() => {
        const initialFilters = { ...defaultFilters };

        Object.keys(defaultFilters).forEach((key) => {
            const filterKey = key as keyof F;
            const value = filters?.[filterKey];

            if (value !== undefined) {
                initialFilters[filterKey] = value as F[keyof F];
            }
        });

        return initialFilters;
    });

    const isFirstRender = useRef(true);
    const prevValuesRef = useRef({
        columnFilters,
        sortKey,
        sortDirection,
    });

    const hasActiveFilters = Object.keys(defaultFilters).some((key) => {
        const currentVal = columnFilters[key as keyof F];
        const defaultVal = defaultFilters[key as keyof F];
        return String(currentVal ?? '') !== String(defaultVal ?? '');
    });

    const isSortChanged =
        sortKey !== defaultSortKey || sortDirection !== defaultSortDirection;

    const isPageChanged = currentPage > 1;
    const isPerPageChanged = perPage !== defaultPerPage;

    const showReset =
        hasActiveFilters || isSortChanged || isPerPageChanged || isPageChanged;

    const navigate = useCallback(
        (customPayload: Record<string, string> = {}) => {
            const payload: Record<string, string> = {};

            Object.entries(columnFilters).forEach(([key, value]) => {
                const defaultVal = defaultFilters[key as keyof F];
                if (
                    value !== undefined &&
                    value !== null &&
                    String(value) !== '' &&
                    String(value) !== String(defaultVal)
                ) {
                    payload[key] = String(value);
                }
            });

            if (
                sortKey !== defaultSortKey ||
                sortDirection !== defaultSortDirection
            ) {
                payload.sort = sortKey;
                payload.direction = sortDirection;
            }

            if (perPage !== defaultPerPage) {
                payload.per_page = String(perPage);
            }

            const finalPayload = { ...payload, ...customPayload };

            router.get(endpoint, finalPayload, {
                preserveState: true,
                preserveScroll: true,
                preserveUrl: true,
                ...(only && only.length > 0 ? { only } : {}),
            });
        },
        [
            columnFilters,
            defaultFilters,
            sortKey,
            defaultSortKey,
            sortDirection,
            defaultSortDirection,
            perPage,
            defaultPerPage,
            endpoint,
            only,
        ],
    );

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const hasChanged =
            JSON.stringify(prevValuesRef.current.columnFilters) !==
                JSON.stringify(columnFilters) ||
            prevValuesRef.current.sortKey !== sortKey ||
            prevValuesRef.current.sortDirection !== sortDirection;

        if (!hasChanged) {
            return;
        }

        prevValuesRef.current = {
            columnFilters,
            sortKey,
            sortDirection,
        };

        const timeout = setTimeout(() => {
            navigate({ page: '1' });
        }, debounceMs);

        return () => clearTimeout(timeout);
    }, [columnFilters, sortKey, sortDirection, navigate, debounceMs]);

    const handlePerPageChange = (newPerPage: number | 'all') => {
        setPerPage(newPerPage);
        navigate({
            per_page: String(newPerPage),
            page: '1',
        });
    };

    const handlePageChange = (targetPage: number) => {
        if (
            targetPage < 1 ||
            targetPage > lastPage ||
            targetPage === currentPage
        ) {
            return;
        }

        navigate({
            page: String(targetPage),
        });
    };

    const handleSort = (key: string) => {
        if (sortKey === key) {
            setSortDirection((prev) => (prev === 'asc' ? 'desc' : 'asc'));
            return;
        }

        setSortKey(key);
        setSortDirection('asc');
    };

    const updateFilter = <K extends keyof F>(key: K, value: F[K]) => {
        setColumnFilters((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    const updateFilters = (partial: Partial<F>) => {
        setColumnFilters((prev) => ({
            ...prev,
            ...partial,
        }));
    };

    const handleReset = () => {
        setColumnFilters(defaultFilters);
        setSortKey(defaultSortKey);
        setSortDirection(defaultSortDirection);
        setPerPage(defaultPerPage);

        prevValuesRef.current = {
            columnFilters: defaultFilters,
            sortKey: defaultSortKey,
            sortDirection: defaultSortDirection,
        };

        const resetPayload: Record<string, string> = {
            page: '1',
            per_page: String(defaultPerPage),
            sort: defaultSortKey,
            direction: defaultSortDirection,
        };

        Object.keys(defaultFilters).forEach((key) => {
            resetPayload[key] = String(defaultFilters[key as keyof F] ?? '');
        });

        if (cleanUrlOnReset) {
            router.get(
                endpoint,
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    preserveUrl: false,
                    ...(only && only.length > 0 ? { only } : {}),
                },
            );
        } else {
            navigate(resetPayload);
        }
    };

    return {
        rows,
        isPaginated,
        total,
        currentPage,
        lastPage,
        perPage,
        sortKey,
        sortDirection,
        columnFilters,
        showReset,
        handleSort,
        updateFilter,
        updateFilters,
        handleReset,
        handlePageChange,
        handlePerPageChange,
        navigate,
    };
}
