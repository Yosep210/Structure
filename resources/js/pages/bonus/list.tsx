import {
    FilterDate,
    SortableFilterableColumn,
    DataTable,
} from '@/components/table';
import { formatCurrency } from '@/lib/format';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, Link, router, setLayoutProps } from '@inertiajs/react';
import {
    ChartNoAxesColumn,
    CreditCard,
    Eye,
    History,
    Home,
} from 'lucide-react';
import {
    bonusHistory,
    statementMember,
} from '@/actions/App/Modules/Bonus/Http/Controllers/BonusController';
import {
    list as bonusList,
    statements as bonusStatement,
} from '@/routes/bonus';
import TableContainer from '@/components/table/paginates/table-container';

type Bonus = {
    id: number;
    username?: string;
    name?: string;
    amount?: string | number;
};

type ColumnFilters = {
    date_from?: string;
    date_to?: string;
    username: string;
    name: string;
    amount_min: string;
    amount_max: string;
};

type Props = {
    totalBonus?: number | string;
    isStatement?: boolean;
    bonus?: Bonus[] | PaginatedData<Bonus>;
    username?: string | null;
    defaultFilters?: Pick<ColumnFilters, 'date_from' | 'date_to'> | null;
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    date_from: '',
    date_to: '',
    username: '',
    name: '',
    amount_min: '',
    amount_max: '',
};

export default function BonusList({
    totalBonus = 0,
    isStatement,
    bonus,
    filters,
    defaultFilters: filterDefaults,
}: Props) {
    const pageTitle = isStatement ? 'Statement Komisi' : 'Bonus Detail';
    const currentUrl = isStatement ? bonusStatement.url() : bonusList.url();
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
                title: pageTitle,
                href: currentUrl,
            },
        ],
    });

    const handleDetail = (username: string) => {
        if (isStatement) {
            router.get(statementMember.url(username), {
                date_from: columnFilters.date_from || filters?.date_from,
                date_to: columnFilters.date_to || filters?.date_to,
            });
        } else {
            router.visit(bonusHistory.url(username));
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
    } = useTableQuery<Bonus, ColumnFilters>({
        endpoint: currentUrl,
        data: bonus,
        filters,
        defaultSortKey: 'amount',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: {
            ...defaultColumnFilters,
            date_from: filterDefaults?.date_from ?? '',
            date_to: filterDefaults?.date_to ?? '',
        },
        only: ['bonus', 'filters'],
        cleanUrlOnReset: Boolean(isStatement),
    });

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
            sortKey: 'amount',
            filter: {
                type: 'range',
                minKey: 'amount_min',
                maxKey: 'amount_max',
            },
        },
    ];

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    return (
        <>
            <Head title={pageTitle} />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {pageTitle}
                                {isStatement && ' (Summary)'}
                            </h1>
                        </div>
                    </div>

                    {!isStatement &&
                        totalBonus !== null &&
                        totalBonus !== undefined && (
                            <div className="inline-flex items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs">
                                Total Bonus : {formatCurrency(totalBonus)}
                            </div>
                        )}
                </div>

                {/* [MODE 1 - KHUSUS BONUS LIST]: Tab Menu Total vs History */}
                {!isStatement && (
                    <div className="flex items-center gap-2">
                        <div className="inline-flex rounded-lg bg-muted p-1">
                            <Link
                                href={bonusList.url()}
                                className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                            >
                                <ChartNoAxesColumn className="h-4 w-4" />
                                Total Bonus Member
                            </Link>
                            <Link
                                href={bonusHistory.url()}
                                className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                            >
                                <History className="h-4 w-4" />
                                History Bonus Member
                            </Link>
                        </div>
                    </div>
                )}

                {/* [MODE 2 - KHUSUS BONUS STATEMENT]: Filter Rentang Periode Tanggal */}
                {isStatement && (
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="inline-flex items-center gap-2.5 rounded-lg border border-border bg-card px-3 py-1.5 shadow-xs">
                            <span className="text-xs font-semibold tracking-wider text-foreground uppercase">
                                Periode:
                            </span>
                            <div className="w-56">
                                <FilterDate
                                    from={columnFilters.date_from}
                                    to={columnFilters.date_to}
                                    onChange={(from, to) =>
                                        updateFilters({
                                            date_from: from,
                                            date_to: to,
                                        })
                                    }
                                />
                            </div>
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
                                <td className="px-4 py-3 text-center font-medium text-foreground uppercase">
                                    {row.username ?? '-'}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {row.name ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {formatCurrency(row.amount)}
                                </td>
                                <td className="px-4 py-3">
                                    <button
                                        type="button"
                                        title="Detail"
                                        aria-label="Lihat Bonus Detail member"
                                        className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                        onClick={() =>
                                            handleDetail(row.username as string)
                                        }
                                    >
                                        <Eye className="h-3.5 w-3.5" />
                                    </button>
                                </td>
                            </>
                        )}
                        emptyIcon={CreditCard}
                    />
                </TableContainer>
            </div>
        </>
    );
}
