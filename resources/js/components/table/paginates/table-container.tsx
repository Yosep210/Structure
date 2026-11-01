import type { ReactNode } from 'react';
import { Pagination } from '@/components/table/paginates/pagination';
import type { PaginationData } from '@/components/table/paginates/pagination';

type Props = {
    children: ReactNode;
    data?: PaginationData | null;
    perPage?: number | 'all';
    onPageChange?: (page: number) => void;
    onPerPageChange?: (perPage: number | 'all') => void;
};

export default function TableContainer({
    children,
    data,
    perPage,
    onPageChange,
    onPerPageChange,
}: Props) {
    return (
        <div className="rounded-xl border border-border bg-card shadow-sm">
            {/* Top Pagination */}
            <Pagination
                data={data}
                perPage={perPage}
                onPageChange={onPageChange}
                onPerPageChange={onPerPageChange}
                className="border-b border-border px-4 py-3"
            />

            <div className="overflow-x-auto">{children}</div>

            {/* Bottom Pagination */}
            <Pagination
                data={data}
                perPage={perPage}
                onPageChange={onPageChange}
                onPerPageChange={onPerPageChange}
                className="border-t border-border px-4 py-3"
            />
        </div>
    );
}
