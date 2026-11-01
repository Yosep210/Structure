import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { type FocusEvent, type ChangeEvent, type KeyboardEvent } from 'react';

export type PaginationData = Pick<
    PaginatedData<unknown>,
    | 'total'
    | 'current_page'
    | 'last_page'
    | 'per_page'
    | 'from'
    | 'to'
    | 'prev_page_url'
    | 'next_page_url'
>;

export type PaginationProps = {
    data?: PaginationData | unknown[] | null;
    perPage?: number | 'all';
    onPageChange?: (page: number) => void;
    onPerPageChange?: (perPage: number | 'all') => void;
    perPageOptions?: (number | 'all')[];
    className?: string;
    showViewOptions?: boolean;
    showTotal?: boolean;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginatedData<T> = {
    data?: T[];
    total?: number;
    current_page?: number;
    last_page?: number;
    per_page?: number | string;
    from?: number;
    to?: number;
    prev_page_url?: string | null;
    next_page_url?: string | null;
    links?: PaginationLink[];
};

export function Pagination({
    data,
    perPage = 10,
    onPageChange,
    onPerPageChange,
    perPageOptions = [10, 25, 50, 100, 'all'],
    className = '',
    showViewOptions = true,
    showTotal = true,
}: PaginationProps) {
    if (!data || Array.isArray(data) || typeof data !== 'object') {
        return null;
    }

    const total = data.total ?? 0;
    const currentPage = data.current_page ?? 1;
    const lastPage = data.last_page ?? 1;
    const currentPerPage =
        perPage === 'all' ? 'all' : (data.per_page ?? perPage);
    const prevPageUrl = data.prev_page_url;
    const nextPageUrl = data.next_page_url;

    const handleInputKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter') {
            const target = Number((event.target as HTMLInputElement).value);
            if (
                Number.isInteger(target) &&
                target >= 1 &&
                target <= lastPage &&
                target !== currentPage
            ) {
                onPageChange?.(target);
            }
        }
    };

    const handleInputBlur = (event: FocusEvent<HTMLInputElement>) => {
        const target = Number(event.target.value);
        if (
            Number.isInteger(target) &&
            target >= 1 &&
            target <= lastPage &&
            target !== currentPage
        ) {
            onPageChange?.(target);
        }
    };

    const handleSelectChange = (event: ChangeEvent<HTMLSelectElement>) => {
        const value = event.target.value;
        const resolved = value === 'all' ? 'all' : Number(value);
        onPerPageChange?.(resolved);
    };

    return (
        <div
            className={`flex flex-wrap items-center justify-between gap-3 text-xs text-muted-foreground ${className}`}
        >
            <div className="flex flex-wrap items-center gap-3">
                <div className="flex items-center gap-1.5">
                    <span className="font-medium text-foreground">Page</span>
                    <div className="flex items-center gap-1">
                        {onPageChange ? (
                            <button
                                type="button"
                                disabled={currentPage <= 1}
                                onClick={() => onPageChange(currentPage - 1)}
                                className={`inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-md border text-sm transition-colors ${
                                    currentPage <= 1
                                        ? 'pointer-events-none cursor-not-allowed border-border bg-muted text-muted-foreground opacity-50'
                                        : 'border-border bg-background text-foreground hover:bg-muted'
                                }`}
                                aria-label="Previous page"
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </button>
                        ) : (
                            <Link
                                href={prevPageUrl ?? '#'}
                                preserveScroll
                                preserveState
                                className={`inline-flex h-7 w-7 items-center justify-center rounded-md border text-sm transition-colors ${
                                    !prevPageUrl || currentPage <= 1
                                        ? 'pointer-events-none cursor-not-allowed border-border bg-muted text-muted-foreground opacity-50'
                                        : 'border-border bg-background text-foreground hover:bg-muted'
                                }`}
                                aria-label="Previous page"
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </Link>
                        )}

                        <input
                            type="text"
                            inputMode="numeric"
                            pattern="[0-9]*"
                            defaultValue={currentPage}
                            key={`page-input-${currentPage}-${lastPage}`}
                            onKeyDown={handleInputKeyDown}
                            onBlur={handleInputBlur}
                            className="h-7 w-12 [appearance:textfield] rounded-md border border-border bg-background text-center font-medium text-foreground outline-none focus:border-primary [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                        />

                        <span className="text-muted-foreground">
                            of{' '}
                            <span className="font-semibold text-foreground">
                                {lastPage}
                            </span>
                        </span>

                        {onPageChange ? (
                            <button
                                type="button"
                                disabled={currentPage >= lastPage}
                                onClick={() => onPageChange(currentPage + 1)}
                                className={`inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-md border text-sm transition-colors ${
                                    currentPage >= lastPage
                                        ? 'pointer-events-none cursor-not-allowed border-border bg-muted text-muted-foreground opacity-50'
                                        : 'border-border bg-background text-foreground hover:bg-muted'
                                }`}
                                aria-label="Next page"
                            >
                                <ChevronRight className="h-4 w-4" />
                            </button>
                        ) : (
                            <Link
                                href={nextPageUrl ?? '#'}
                                preserveScroll
                                preserveState
                                className={`inline-flex h-7 w-7 items-center justify-center rounded-md border text-sm transition-colors ${
                                    !nextPageUrl || currentPage >= lastPage
                                        ? 'pointer-events-none cursor-not-allowed border-border bg-muted text-muted-foreground opacity-50'
                                        : 'border-border bg-background text-foreground hover:bg-muted'
                                }`}
                                aria-label="Next page"
                            >
                                <ChevronRight className="h-4 w-4" />
                            </Link>
                        )}
                    </div>
                </div>

                {showViewOptions && onPerPageChange && (
                    <>
                        <span className="text-border">|</span>

                        <div className="flex items-center gap-1.5">
                            <span>View</span>
                            <select
                                value={currentPerPage}
                                onChange={handleSelectChange}
                                className="h-7 cursor-pointer rounded-md border border-border bg-background px-2 py-0.5 text-xs font-medium text-foreground outline-none focus:border-primary"
                            >
                                {perPageOptions.map((option) => (
                                    <option key={option} value={option}>
                                        {option === 'all' ? 'All' : option}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </>
                )}

                {showTotal && (
                    <>
                        <span className="text-border">|</span>

                        <span>
                            Found total{' '}
                            <span className="font-semibold text-foreground">
                                {total}
                            </span>{' '}
                            records
                        </span>
                    </>
                )}
            </div>
        </div>
    );
}

export default Pagination;
