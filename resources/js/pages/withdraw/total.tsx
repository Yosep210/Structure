import { DataTable, SortableFilterableColumn } from '@/components/table';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { formatCurrency, formatDate } from '@/lib/format';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { Head, Link, setLayoutProps } from '@inertiajs/react';
import { CreditCard, DollarSign, Home } from 'lucide-react';
import {
    withdrawList,
    withdrawTotal,
} from '@/actions/App/Modules/Withdraw/Http/Controllers/WithdrawController';
import TableContainer from '@/components/table/paginates/table-container';

type Withdraw = {
    id: number;
    datewd?: string;
    trx?: string;
    total_nominal?: string | number;
    total_receipt?: string | null;
    total_admin?: string | null;
    total_auto_ro?: string | null;
    total_transferred?: string | number;
};

type ColumnFilters = {
    datewd_from: string;
    datewd_to: string;
    trx_min: string;
    trx_max: string;
    total_nominal_min: string;
    total_nominal_max: string;
    total_receipt_min: string;
    total_receipt_max: string;
    total_admin_min: string;
    total_admin_max: string;
    total_auto_ro_min: string;
    total_auto_ro_max: string;
    total_transferred_min: string;
    total_transferred_max: string;
};

type Props = {
    totalWd?: string | number;
    totalReceipt?: string | number;
    totalTransfer?: string | number;
    withdraws?: Withdraw[] | PaginatedData<Withdraw>;
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    datewd_from: '',
    datewd_to: '',
    trx_min: '',
    trx_max: '',
    total_nominal_min: '',
    total_nominal_max: '',
    total_receipt_min: '',
    total_receipt_max: '',
    total_admin_min: '',
    total_admin_max: '',
    total_auto_ro_min: '',
    total_auto_ro_max: '',
    total_transferred_min: '',
    total_transferred_max: '',
};

export default function WithdrawList({
    totalWd,
    totalReceipt,
    totalTransfer,
    withdraws,
    filters,
}: Props) {
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
                title: 'Withdraw',
                href: withdrawTotal(),
            },
        ],
    });

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
    } = useTableQuery<Withdraw, ColumnFilters>({
        endpoint: withdrawTotal.url(),
        data: withdraws,
        filters,
        defaultSortKey: 'datewd',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['withdraws'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Tanggal',
            sortKey: 'datewd',
            filter: {
                type: 'date',
                fromKey: 'datewd_from',
                toKey: 'datewd_to',
            },
        },
        {
            label: 'Transaksi',
            sortKey: 'trx',
            filter: {
                type: 'range',
                minKey: 'trx_min',
                maxKey: 'trx_max',
            },
        },
        {
            label: 'Keterangan',
            sortKey: 'total_nominal',
            filter: {
                type: 'range',
                minKey: 'total_nominal_min',
                maxKey: 'total_nominal_max',
            },
        },
        {
            label: 'Nominal',
            sortKey: 'total_receipt',
            filter: {
                type: 'range',
                minKey: 'total_receipt_min',
                maxKey: 'total_receipt_max',
            },
        },
        {
            label: 'Fee Transfer',
            sortKey: 'total_admin',
            filter: {
                type: 'range',
                minKey: 'total_admin_min',
                maxKey: 'total_admin_max',
            },
        },
        {
            label: 'Nominal Transferred',
            sortKey: 'total_transferred',
            filter: {
                type: 'range',
                minKey: 'total_transferred_min',
                maxKey: 'total_transferred_max',
            },
        },
    ];

    return (
        <>
            <Head title="Withdraw" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                Withdraw
                            </h1>
                        </div>
                    </div>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <div className="flex justify-between gap-1">
                                <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                    Total Withdraw
                                </span>
                                <CreditCard className="h-4 w-4 text-green-500" />
                            </div>
                            <div className="mt-2 mb-2 flex items-center">
                                <span className="font-mono text-2xl font-extrabold">
                                    {formatCurrency(totalWd)}
                                </span>
                            </div>
                            <span className="block text-[11px] font-semibold tracking-wider text-muted-foreground">
                                Total Withdrawl
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <div className="flex justify-between gap-1">
                                <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                    Nilai Transfer
                                </span>
                                <CreditCard className="h-4 w-4 text-red-500" />
                            </div>
                            <div className="mt-2 mb-2 flex items-center">
                                <span className="font-mono text-2xl font-extrabold">
                                    {formatCurrency(totalReceipt)}
                                </span>
                            </div>
                            <span className="block text-[11px] font-semibold tracking-wider text-muted-foreground">
                                Saldo e-Wallet-Pajak-Fee
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <div className="flex justify-between gap-1">
                                <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                    Total Transfer
                                </span>
                                <CreditCard className="h-4 w-4 text-blue-500" />
                            </div>
                            <div className="mt-2 mb-2 flex items-center">
                                <span className="font-mono text-2xl font-extrabold">
                                    {formatCurrency(totalTransfer)}
                                </span>
                            </div>
                            <span className="block text-[11px] font-semibold tracking-wider text-muted-foreground">
                                Total Nilai yang telah di transfer
                            </span>
                        </div>
                    </div>
                </div>

                {/* Tab Menu */}
                <div className="flex items-center gap-2">
                    <div className="inline-flex rounded-lg bg-muted p-1">
                        <Link
                            href={withdrawList()}
                            className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                        >
                            List Withdraw
                        </Link>
                        <Link
                            href={withdrawTotal()}
                            className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                        >
                            List Total Transaksi
                        </Link>
                    </div>
                </div>

                <TableContainer
                    data={
                        withdraws && !Array.isArray(withdraws)
                            ? withdraws
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
                                <td className="px-4 py-3 text-center text-xs whitespace-nowrap">
                                    {formatDate(row.datewd)}
                                </td>
                                <td className="px-4 py-3 text-center font-medium whitespace-nowrap">
                                    {row.trx}
                                </td>
                                <td className="px-4 py-3 text-left">
                                    <div className="flex flex-col gap-0.5 text-xs whitespace-nowrap">
                                        <span className="font-mono text-foreground">
                                            Withdrawl :{' '}
                                            {formatCurrency(row.total_nominal)}
                                        </span>
                                        {row.total_admin && (
                                            <span className="font-mono text-foreground">
                                                Fee :{' '}
                                                <span className="font-medium text-foreground uppercase">
                                                    {formatCurrency(
                                                        row.total_admin,
                                                    )}
                                                </span>
                                            </span>
                                        )}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium whitespace-nowrap">
                                    {formatCurrency(row.total_receipt)}
                                </td>
                                <td className="px-4 py-3 text-center font-medium whitespace-nowrap">
                                    {formatCurrency(row.total_admin)}
                                </td>
                                <td className="px-4 py-3 text-center font-medium whitespace-nowrap">
                                    {formatCurrency(row.total_transferred)}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    {/* <button
                                        type="button"
                                        title="Detail"
                                        aria-label="Lihat Withdraw member"
                                        className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold shadow-xs transition-all active:scale-98"
                                    >
                                        <Eye className="h-3.5 w-3.5" />
                                    </button> */}
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
