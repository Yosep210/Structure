import {
    DataTable,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import { formatCurrency, formatMonth } from '@/lib/format';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { dashboard } from '@/routes';
import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/react';
import {
    ChartNoAxesColumn,
    CreditCard,
    DollarSign,
    Eye,
    History,
    Home,
    Percent,
} from 'lucide-react';
import {
    autoRoHistory,
    autoRoList,
    autoRoMonthly,
} from '@/actions/App/Modules/AutoRo/Http/Controllers/AutoRoController';
import TableContainer from '@/components/table/paginates/table-container';

type Monthlies = {
    id: number;
    month_ro?: string;
    username?: string;
    name?: string;
    total_ro: string | number | null;
};

type ColumnFilters = {
    month_ro: string;
    username: string;
    name: string;
    total_ro_min: string;
    total_ro_max: string;
};

type PageProps = {
    auth?: {
        user?: {
            name?: string | null;
            username?: string | null;
            roles?: string[];
        } | null;
    };
};

type Props = {
    monthlies?: Monthlies[] | PaginatedData<Monthlies>;
    months?: Option[];
    totalRo?: number | string | null;
    totalRoMonth?: number | string | null;
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    month_ro: '',
    username: '',
    name: '',
    total_ro_max: '',
    total_ro_min: '',
};

export default function MonthlyList({
    monthlies,
    months,
    totalRo,
    totalRoMonth,
    filters,
}: Props) {
    const { auth } = usePage<PageProps>().props;
    const isAdmin = auth?.user?.roles?.includes('admin') ?? false;

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
                href: autoRoMonthly(),
            },
        ],
    });

    const handleDetail = (username: string) => {
        router.visit(
            autoRoHistory.url(username, { query: { from: 'monthly' } }),
        );
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
    } = useTableQuery<Monthlies, ColumnFilters>({
        endpoint: autoRoMonthly.url(),
        data: monthlies,
        filters,
        defaultSortKey: 'month_ro',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['monthlies'],
    });

    const member: SortableFilterableColumn<ColumnFilters>[] = [
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
    ];

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Bulan',
            sortKey: 'month_ro',
            filter: {
                type: 'select',
                key: 'month_ro',
                options: months ?? [],
            },
        },
        ...(isAdmin ? member : []),
        {
            label: 'Jumlah',
            sortKey: 'total_ro',
            filter: {
                type: 'range',
                minKey: 'total_ro_min',
                maxKey: 'total_ro_max',
            },
        },
    ];

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

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

                {/* [MODE 2 - KHUSUS MEMBER]: Ringkasan Total RO & Total RO Bulan Ini */}
                {!isAdmin && totalRo !== undefined && totalRo !== null && (
                    <div className="grid auto-rows-min gap-4 md:grid-cols-2">
                        <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                            <div>
                                <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                    Total Auto RO
                                </span>
                                <div className="mt-2 flex items-center justify-between gap-1">
                                    <span className="font-mono text-2xl font-extrabold">
                                        {formatCurrency(totalRo)}
                                    </span>
                                    <ChartNoAxesColumn className="h-4 w-4 text-green-500" />
                                </div>
                            </div>
                        </div>
                        <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                            <div>
                                <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                    Total Auto RO (Bulan Ini)
                                </span>
                                <div className="mt-2 flex items-center justify-between gap-1">
                                    <span className="font-mono text-2xl font-extrabold">
                                        {formatCurrency(totalRoMonth)}
                                    </span>
                                    <Percent className="h-4 w-4 text-blue-500" />
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Tab Menu */}
                <div className="flex items-center gap-2">
                    <div className="inline-flex rounded-lg bg-muted p-1">
                        {/* [MODE 1 - KHUSUS ADMIN]: Tab Saldo Auto RO */}
                        {isAdmin && (
                            <Link
                                href={autoRoList.url()}
                                className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                            >
                                <CreditCard className="h-4 w-4" />
                                Saldo Auto RO
                            </Link>
                        )}
                        <Link
                            href={autoRoMonthly.url()}
                            className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
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
                    data={
                        monthlies && !Array.isArray(monthlies)
                            ? monthlies
                            : undefined
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
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {formatMonth(row.month_ro)}
                                    </div>
                                </td>
                                {/* [MODE 1 - KHUSUS ADMIN]: Kolom Data Username & Nama */}
                                {isAdmin && (
                                    <>
                                        <td className="px-4 py-3 text-center font-medium text-foreground uppercase">
                                            {row.username ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.name ?? '-'}
                                            </div>
                                        </td>
                                    </>
                                )}
                                <td className="px-4 py-3 text-center font-medium">
                                    {formatCurrency(row.total_ro)}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex items-center justify-center">
                                        {/* [MODE 1 - KHUSUS ADMIN]: Tombol Aksi Detail Drill-down */}
                                        {isAdmin && (
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
                                        )}
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
