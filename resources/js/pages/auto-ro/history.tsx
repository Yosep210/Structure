import type { PaginatedData } from '@/components/table/paginates/pagination';
import {
    DataTable,
    EnumBadge,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { formatCurrency, formatDate } from '@/lib/format';
import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/react';
import {
    ArrowLeftToLine,
    ChartNoAxesColumn,
    CreditCard,
    DollarSign,
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

type Histories = {
    id: number;
    created_at?: string;
    username?: string;
    name?: string;
    type: string | number;
    nominal?: string | number | null;
    amount: string | number | null;
    description: string;
};

type ColumnFilters = {
    created_at_from: string;
    created_at_to: string;
    username: string;
    name: string;
    type: string;
    nominal_min: string;
    nominal_max: string;
    amount_min: string;
    amount_max: string;
    description: string;
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
    histories?: Histories[] | PaginatedData<Histories>;
    types?: Option[];
    targetMember?: {
        id: number;
        name: string;
        username: string;
    } | null;
    totalRo?: number | string | null;
    totalRoMonth?: number | string | null;
    autoRoBalance?: number | string | null;
    autoRoIn?: number | string | null;
    autoRoOut?: number | string | null;
    from?: string;
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    created_at_from: '',
    created_at_to: '',
    username: '',
    name: '',
    type: '',
    nominal_min: '',
    nominal_max: '',
    amount_max: '',
    amount_min: '',
    description: '',
};

export default function HistoryList({
    histories,
    types,
    targetMember,
    totalRo,
    totalRoMonth,
    autoRoBalance,
    autoRoIn,
    autoRoOut,
    from,
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
                href: autoRoHistory(),
            },
        ],
    });

    const endpoint = targetMember?.username
        ? autoRoHistory.url(targetMember.username, {
              query: from ? { from } : undefined,
          })
        : autoRoHistory.url();

    const handleBack = () => {
        if (from === 'monthly') {
            router.visit(autoRoMonthly.url());
        } else {
            router.visit(autoRoList.url());
        }
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
    } = useTableQuery<Histories, ColumnFilters>({
        endpoint,
        data: histories,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['histories'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    {
        /* [MODE 1 - KHUSUS ADMIN GLOBAL]: Kolom Sort Username, Nama, & Tipe */
    }
    const global: SortableFilterableColumn<ColumnFilters>[] = [
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
            label: 'Tipe',
            sortKey: 'type',
            filter: {
                type: 'select',
                key: 'type',
                options: types ?? [],
            },
        },
    ];

    {
        /* [MODE 2 - KHUSUS MEMBER & ADMIN DRILL-DOWN DARI MONTHLY]: Kolom Sort Bonus */
    }
    const monthly: SortableFilterableColumn<ColumnFilters> = {
        label: 'Bonus',
        sortKey: 'nominal',
        filter: {
            type: 'range',
            minKey: 'nominal_min',
            maxKey: 'nominal_max',
        },
    };

    {
        /* [MODE 3 - KHUSUS ADMIN DRILL-DOWN DARI LIST]: Kolom Sort Tipe */
    }
    const fromlist: SortableFilterableColumn<ColumnFilters> = {
        label: 'Tipe',
        sortKey: 'type',
        filter: {
            type: 'select',
            key: 'type',
            options: types ?? [],
        },
    };

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Tanggal',
            sortKey: 'created_at',
            filter: {
                type: 'date',
                fromKey: 'created_at_from',
                toKey: 'created_at_to',
            },
        },
        ...(isAdmin && !targetMember ? global : []),
        ...((targetMember && from === 'monthly') || (!isAdmin && !targetMember)
            ? [monthly]
            : []),
        ...(targetMember && from !== 'monthly' ? [fromlist] : []),
        {
            label: 'Jumlah',
            sortKey: 'amount',
            filter: {
                type: 'range',
                minKey: 'amount_min',
                maxKey: 'amount_max',
            },
        },
        {
            label: 'Keterangan',
            sortKey: 'description',
            filter: { type: 'input', key: 'description' },
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
                        {/* [MODE 2 - KHUSUS ADMIN DETAIL MEMBER]: Info Bar Member */}
                        {targetMember && (
                            <div className="flex items-center gap-2">
                                <span className="text-sm font-bold tracking-tight text-foreground">
                                    Member : {targetMember?.name}
                                </span>
                                ({targetMember?.username})
                            </div>
                        )}
                    </div>
                    {/* [MODE 2 - KHUSUS ADMIN DETAIL MEMBER]: Tombol Kembali */}
                    {targetMember && (
                        <div className="flex justify-end">
                            <button
                                type="button"
                                title="Kembali"
                                aria-label="Kembali ke Rekap Auto RO"
                                className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                onClick={() => handleBack()}
                            >
                                <ArrowLeftToLine className="h-3.5 w-3.5" />
                                Kembali
                            </button>
                        </div>
                    )}
                </div>

                {/* [MODE 2 - KHUSUS MEMBER / ADMIN DRILL-DOWN DARI MONTHLY]: Kartu Total Auto RO & Bulan Ini */}
                {((targetMember && from === 'monthly') ||
                    (!isAdmin && !targetMember)) &&
                    totalRo !== undefined &&
                    totalRo !== null && (
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

                {/* [MODE 3 - KHUSUS ADMIN DRILL-DOWN DARI LIST]: Kartu Saldo Auto RO, IN & OUT */}
                {targetMember &&
                    from === 'list' &&
                    autoRoBalance !== undefined &&
                    autoRoBalance !== null && (
                        <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                            <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                                <div>
                                    <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                        Saldo Auto RO
                                    </span>
                                    <div className="mt-2 flex items-center justify-between gap-1">
                                        <span className="font-mono text-2xl font-extrabold">
                                            {formatCurrency(autoRoBalance)}
                                        </span>
                                        <CreditCard className="h-4 w-4 text-primary" />
                                    </div>
                                </div>
                            </div>
                            <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                                <div>
                                    <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                        Total IN Auto RO
                                    </span>
                                    <div className="mt-2 flex items-center justify-between gap-1">
                                        <span className="font-mono text-2xl font-extrabold text-green-600">
                                            {formatCurrency(autoRoIn)}
                                        </span>
                                        <ChartNoAxesColumn className="h-4 w-4 text-green-500" />
                                    </div>
                                </div>
                            </div>
                            <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                                <div>
                                    <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                        Total OUT Auto RO
                                    </span>
                                    <div className="mt-2 flex items-center justify-between gap-1">
                                        <span className="font-mono text-2xl font-extrabold text-red-600">
                                            {formatCurrency(autoRoOut)}
                                        </span>
                                        <Percent className="h-4 w-4 text-red-500" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                {/* Tab Menu */}
                {!targetMember && (
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
                                className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                            >
                                <ChartNoAxesColumn className="h-4 w-4" />
                                Total Auto RO (Bulanan)
                            </Link>
                            <Link
                                href={autoRoHistory.url()}
                                className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                            >
                                <History className="h-4 w-4" />
                                Auto RO IN/OUT (History)
                            </Link>
                        </div>
                    </div>
                )}

                <TableContainer
                    data={
                        histories && !Array.isArray(histories)
                            ? histories
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
                                <td className="px-4 py-3 text-center">
                                    {formatDate(row.created_at)}
                                </td>
                                {/* [MODE 1 - KHUSUS ADMIN GLOBAL]: Kolom Data Username, Nama, & Tipe */}
                                {isAdmin && !targetMember && (
                                    <>
                                        <td className="px-4 py-3 text-left font-medium text-foreground uppercase">
                                            {row.username ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.name ?? '-'}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <EnumBadge
                                                options={types}
                                                value={row.type}
                                            />
                                        </td>
                                    </>
                                )}
                                {/* [MODE 2 - KHUSUS MEMBER & ADMIN DRILL-DOWN DARI MONTHLY]: Kolom Data Bonus */}
                                {((targetMember && from === 'monthly') ||
                                    (!isAdmin && !targetMember)) && (
                                    <td className="px-4 py-3 text-center font-medium">
                                        {formatCurrency(row.nominal)}
                                    </td>
                                )}
                                {/* [MODE 3 - KHUSUS ADMIN DRILL-DOWN DARI LIST]: Kolom Data Tipe */}
                                {targetMember && from !== 'monthly' && (
                                    <td className="px-4 py-3 text-center">
                                        <EnumBadge
                                            options={types}
                                            value={row.type}
                                        />
                                    </td>
                                )}
                                <td className="px-4 py-3 text-center font-medium">
                                    {formatCurrency(row.amount)}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {row.description ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center"></td>
                            </>
                        )}
                        emptyIcon={DollarSign}
                    />
                </TableContainer>
            </div>
        </>
    );
}
