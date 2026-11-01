import {
    generationList,
    sponsorList,
} from '@/actions/App/Modules/Member/Http/Controllers/MemberController';
import {
    DataTable,
    SortableFilterableColumn,
    EnumBadge,
    type Option,
} from '@/components/table';
import TableContainer from '@/components/table/paginates/table-container';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, router, setLayoutProps } from '@inertiajs/react';
import { Home, Network, Users } from 'lucide-react';

type Sponsor = {
    id: number;
    username: string;
    name?: string;
    status?: number | string;
    total_active?: number;
    total_non_active?: number;
    total_sponsored?: number;
    created_at?: string;
    last_login?: string;
    rank_id?: number | string | null;
};

type ColumnFilters = {
    username: string;
    name: string;
    rank_id: string;
    status: string;
    total_active_min: string;
    total_active_max: string;
    total_non_active_min: string;
    total_non_active_max: string;
    created_at_from: string;
    created_at_to: string;
};

type Props = {
    sponsor?: Sponsor[] | PaginatedData<Sponsor>;
    ranks?: Option[];
    statuses?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    username: '',
    name: '',
    rank_id: '',
    status: '',
    total_active_min: '',
    total_active_max: '',
    total_non_active_min: '',
    total_non_active_max: '',
    created_at_from: '',
    created_at_to: '',
};

export default function SponsorList({
    sponsor,
    ranks,
    statuses,
    filters,
}: Props) {
    setLayoutProps({
        breadcrumbs: [
            {
                icon: Home,
                href: dashboard(),
            },
            {
                title: 'Member',
                href: '#',
            },
            {
                title: 'List Sponsor',
                href: sponsorList(),
            },
        ],
    });

    const handleGen = (username: string) => {
        router.visit(generationList.url(username));
    };

    const {
        rows,
        currentPage,
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
    } = useTableQuery<Sponsor, ColumnFilters>({
        endpoint: sponsorList.url(),
        data: sponsor,
        filters,
        defaultSortKey: 'total_sponsored',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['sponsor'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Username',
            sortKey: 'username',
            filter: { type: 'input', key: 'username' },
        },
        {
            label: 'Nama',
            sortKey: 'name',
            filter: { type: 'input', key: 'name' },
        },
        {
            label: 'Rank',
            sortKey: 'rank_id',
            filter: { type: 'select', key: 'rank_id', options: ranks ?? [] },
        },
        {
            label: 'Member Aktif',
            sortKey: 'total_active',
            filter: {
                type: 'range',
                minKey: 'total_active_min',
                maxKey: 'total_active_max',
            },
        },
        {
            label: 'Member Tidak Aktif',
            sortKey: 'total_non_active',
            filter: {
                type: 'range',
                minKey: 'total_non_active_min',
                maxKey: 'total_non_active_max',
            },
        },
        {
            label: 'Status',
            sortKey: 'status',
            filter: {
                type: 'select',
                key: 'status',
                options: statuses ?? [],
            },
        },
        {
            label: 'Tanggal Daftar',
            sortKey: 'created_at',
            filter: {
                type: 'date',
                fromKey: 'created_at_from',
                toKey: 'created_at_to',
            },
        },
    ];

    return (
        <>
            <Head title="List Sponsor" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                List Sponsor
                            </h1>
                        </div>
                    </div>
                </div>

                <TableContainer
                    data={
                        sponsor && !Array.isArray(sponsor) ? sponsor : undefined
                    }
                    perPage={perPage}
                    onPageChange={handlePageChange}
                    onPerPageChange={handlePerPageChange}
                >
                    <DataTable
                        rows={rows}
                        columns={columns}
                        filters={columnFilters}
                        onFilterChange={(key, value) =>
                            updateFilter(key, value)
                        }
                        onFiltersChange={updateFilters}
                        sortKey={sortKey}
                        sortDirection={sortDirection}
                        onSort={handleSort}
                        showReset={showReset}
                        onReset={handleReset}
                        rowNumberStart={rowStart}
                        getRowKey={(row) => row.id}
                        renderRow={(row) => (
                            <>
                                <td className="px-4 py-3 text-center font-medium text-foreground">
                                    {row.username ?? '-'}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {row.name ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        <EnumBadge
                                            options={ranks}
                                            value={row.rank_id}
                                        />
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <span className="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 ring-inset dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30">
                                        {row.total_active ?? 0}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <span className="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-600/20 ring-inset dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30">
                                        {row.total_non_active ?? 0}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <EnumBadge
                                        options={statuses}
                                        value={row.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-center">
                                    {formatDate(row.created_at)}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center justify-center gap-1.5">
                                        <button
                                            type="button"
                                            title="Generation"
                                            aria-label="Lihat Generation Member"
                                            className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                            onClick={() =>
                                                handleGen(
                                                    row.username as string,
                                                )
                                            }
                                        >
                                            <Network className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </>
                        )}
                        emptyIcon={Users}
                    />
                </TableContainer>
            </div>
        </>
    );
}
