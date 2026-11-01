import type { PaginatedData } from '@/components/table/paginates/pagination';
import { DataTable, SortableFilterableColumn } from '@/components/table';
import { formatCurrency } from '@/lib/format';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { Head, Link, router, setLayoutProps } from '@inertiajs/react';
import {
    ChartNoAxesColumn,
    CreditCard,
    DollarSign,
    Eye,
    History,
    Home,
} from 'lucide-react';
import {
    autoRoHistory,
    autoRoList,
    autoRoMonthly,
} from '@/actions/App/Modules/AutoRo/Http/Controllers/AutoRoController';
import TableContainer from '@/components/table/paginates/table-container';

type AutoRo = {
    id: number;
    username?: string;
    name?: string;
    bonus_total: string | number | null;
};

type ColumnFilters = {
    username: string;
    name: string;
    bonus_total_min: string;
    bonus_total_max: string;
};

type Props = {
    autoRo?: AutoRo[] | PaginatedData<AutoRo>;
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    username: '',
    name: '',
    bonus_total_max: '',
    bonus_total_min: '',
};

export default function AutoRoList({ autoRo, filters }: Props) {
    setLayoutProps({
        breadcrumbs: [
            {
                icon: Home,
                href: dashboard(),
            },
            {
                title: 'Komisi',
                href: '#',
            },
            {
                title: 'Auto RO',
                href: autoRoList(),
            },
        ],
    });

    const handleDetail = (username: string) => {
        router.visit(autoRoHistory.url(username, { query: { from: 'list' } }));
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
    } = useTableQuery<AutoRo, ColumnFilters>({
        endpoint: autoRoList.url(),
        data: autoRo,
        filters,
        defaultSortKey: 'bonus_total',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['autoRo'],
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
            label: 'Jumlah',
            sortKey: 'bonus_total',
            filter: {
                type: 'range',
                minKey: 'bonus_total_min',
                maxKey: 'bonus_total_max',
            },
        },
    ];

    return (
        <>
            <Head title="Auto RO" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                Auto RO
                            </h1>
                        </div>
                    </div>
                </div>

                {/* Tab Menu */}
                <div className="flex items-center gap-2">
                    <div className="inline-flex rounded-lg bg-muted p-1">
                        <Link
                            href={autoRoList.url()}
                            className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                        >
                            <CreditCard className="h-4 w-4" />
                            Saldo Auto RO
                        </Link>
                        <Link
                            href={autoRoMonthly.url()}
                            className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                        >
                            <ChartNoAxesColumn className="h-4 w-4" />
                            Total Auto RO (Bulanan)
                        </Link>
                        <Link
                            href={autoRoHistory.url()}
                            className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                        >
                            <History className="h-4 w-4" />
                            Auto RO IN/OUT (History)
                        </Link>
                    </div>
                </div>

                <TableContainer
                    data={autoRo && !Array.isArray(autoRo) ? autoRo : undefined}
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
                                <td className="px-4 py-3 text-center font-medium text-foreground uppercase">
                                    {row.username ?? '-'}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {row.name ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {formatCurrency(row.bonus_total)}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex items-center justify-center">
                                        <button
                                            type="button"
                                            title="Detail"
                                            aria-label="Detail Auto RO Member"
                                            className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                            onClick={() =>
                                                handleDetail(
                                                    row.username as string,
                                                )
                                            }
                                        >
                                            <Eye className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </>
                        )}
                        emptyIcon={DollarSign}
                    />
                </TableContainer>
            </div>
        </>
    );
}
