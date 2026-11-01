import {
    EnumBadge,
    type Option,
    SortableFilterableColumn,
    DataTable,
} from '@/components/table';
import { formatDate } from '@/lib/format';
import { formatCurrency } from '@/lib/format';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/react';
import {
    ArrowLeftToLine,
    CreditCard,
    History,
    Home,
    ChartNoAxesColumn,
} from 'lucide-react';
import { bonusHistory } from '@/actions/App/Modules/Bonus/Http/Controllers/BonusController';
import { list as bonusList } from '@/routes/bonus';
import TableContainer from '@/components/table/paginates/table-container';

type Bonus = {
    id: number;
    created_at?: string;
    username?: string;
    name?: string;
    amount?: string | number;
    tax?: string | number;
    amount_tax?: string | number;
    type?: number | string;
    description?: string;
};

type ColumnFilters = {
    created_at_from: string;
    created_at_to: string;
    username: string;
    name: string;
    amount_min: string;
    amount_max: string;
    type: string;
    description: string;
};

type targetMember = {
    name?: string | null;
    username?: string | null;
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
    totalBonus?: number | string;
    bonus?: Bonus[] | PaginatedData<Bonus>;
    targetMember?: targetMember | null;
    types?: Option[];
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
    amount_min: '',
    amount_max: '',
    type: '',
    description: '',
};

export default function BonusHistory({
    totalBonus = 0,
    bonus,
    targetMember,
    types,
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
                title: 'Bonus Detail',
                href: bonusHistory.url(targetMember?.username ?? undefined),
            },
        ],
    });

    const memberName = targetMember?.name ?? '-';
    const memberUsername = targetMember?.username ?? '-';

    const handleBack = () => {
        router.visit(bonusList());
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
    } = useTableQuery<Bonus, ColumnFilters>({
        endpoint: bonusHistory.url(targetMember?.username ?? undefined),
        data: bonus,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['bonus', 'filters'],
    });

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
    ];

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
        ...(!targetMember && isAdmin ? global : []),
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
            label: 'Tipe',
            sortKey: 'type',
            filter: {
                type: 'select',
                key: 'type',
                options: types ?? [],
            },
        },
        {
            label: 'Keterangan',
            sortKey: 'description',
            filter: { type: 'input', key: 'description' },
        },
    ];

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    return (
        <>
            <Head title="Bonus Detail" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                Bonus Detail
                            </h1>
                        </div>
                    </div>

                    <div className="inline-flex items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs">
                        Total Bonus : {formatCurrency(totalBonus)}
                    </div>
                </div>

                {/* [MODE 1 - KHUSUS ADMIN GLOBAL]: Tab Menu Antara Total Bonus & History Bonus */}
                {!targetMember && isAdmin && (
                    <div className="flex items-center gap-2">
                        <div className="inline-flex rounded-lg bg-muted p-1">
                            <Link
                                href={bonusList.url()}
                                className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                            >
                                <ChartNoAxesColumn className="h-4 w-4" />
                                Total Bonus Member
                            </Link>
                            <Link
                                href={bonusHistory.url()}
                                className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                            >
                                <History className="h-4 w-4" />
                                History Bonus Member
                            </Link>
                        </div>
                    </div>
                )}

                {/* [MODE 2 - KHUSUS ADMIN DETAIL MEMBER]: Info Bar Member & Tombol Kembali */}
                {targetMember && isAdmin && (
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold tracking-tight text-foreground">
                                Member : {memberName} ({memberUsername})
                            </span>
                        </div>

                        <div className="flex justify-end">
                            <button
                                type="button"
                                title="Kembali"
                                aria-label="Kembali ke Rekap Bonus"
                                className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                onClick={() => handleBack()}
                            >
                                <ArrowLeftToLine className="h-3.5 w-3.5" />
                                Kembali
                            </button>
                        </div>
                    </div>
                )}

                <TableContainer
                    data={bonus && !Array.isArray(bonus) ? bonus : undefined}
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
                                {/* [MODE 1 - KHUSUS ADMIN GLOBAL]: Kolom Data Username & Nama */}
                                {!targetMember && isAdmin && (
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
                                    {formatCurrency(row.amount)}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-center whitespace-nowrap">
                                        <EnumBadge
                                            options={types}
                                            value={row.type}
                                        />
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-left">
                                    <div className="flex flex-col gap-1 text-xs">
                                        {row.description && (
                                            <div className="font-medium text-foreground">
                                                {row.description}
                                            </div>
                                        )}
                                        <div className="font-mono text-[11px] leading-relaxed text-muted-foreground">
                                            <div className="flex items-center gap-1.5">
                                                <span className="w-16">
                                                    Nominal
                                                </span>
                                                <span>
                                                    :{' '}
                                                    {formatCurrency(row.amount)}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-1.5">
                                                <span className="w-16">
                                                    Pajak
                                                </span>
                                                <span className="text-red-600 dark:text-red-400">
                                                    :{' '}
                                                    {formatCurrency(
                                                        row.tax ?? 0,
                                                    )}
                                                </span>
                                            </div>
                                            <div className="my-1 w-44 border-t border-border" />
                                            <div className="flex items-center gap-1.5 font-semibold text-foreground">
                                                <span className="w-16">
                                                    Bonus
                                                </span>
                                                <span>
                                                    :{' '}
                                                    {formatCurrency(
                                                        row.amount_tax ??
                                                            Number(
                                                                row.amount ?? 0,
                                                            ) -
                                                                Number(
                                                                    row.tax ??
                                                                        0,
                                                                ),
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-4 py-3"></td>
                            </>
                        )}
                        emptyIcon={CreditCard}
                    />
                </TableContainer>
            </div>
        </>
    );
}
