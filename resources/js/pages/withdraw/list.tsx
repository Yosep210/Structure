import {
    DataTable,
    EnumBadge,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogDescription,
    DialogHeader,
    DialogContent,
    DialogTitle,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { formatCurrency, formatDate } from '@/lib/format';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { Form, Head, Link, setLayoutProps, usePage } from '@inertiajs/react';
import { CreditCard, DollarSign, Home } from 'lucide-react';
import { useState } from 'react';
import PasswordInput from '@/components/password-input';
import InputError from '@/components/input-error';
import {
    confirm,
    withdrawList,
    withdrawTotal,
} from '@/actions/App/Modules/Withdraw/Http/Controllers/WithdrawController';
import TableContainer from '@/components/table/paginates/table-container';

type Withdraw = {
    id: number;
    username?: string;
    name?: string;
    type?: string | number;
    bank_code?: string | null;
    bank_name?: string | null;
    bank_account?: string | null;
    bank_account_name?: string | null;
    nominal?: string | number;
    admin_fund?: string | number;
    nominal_receipt?: string | number;
    status?: string | number;
    created_at?: string;
    dateconfirmed?: string;
    confirm_by?: string | number;
};

type ColumnFilters = {
    username: string;
    name: string;
    type: string;
    bank_code: string;
    bank_name: string;
    bank_account: string;
    nominal_min: string;
    nominal_max: string;
    nominal_receipt_min: string;
    nominal_receipt_max: string;
    status: string;
    created_at_from: string;
    created_at_to: string;
    dateconfirmed_from: string;
    dateconfirmed_to: string;
    confirm_by: string;
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
    totalWd?: string | number;
    totalReceipt?: string | number;
    totalTransfer?: string | number;
    ewallet_balance?: string | number | null;
    withdraws?: Withdraw[] | PaginatedData<Withdraw>;
    wd_statuses?: Option[];
    types?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    username: '',
    name: '',
    type: '',
    bank_code: '',
    bank_name: '',
    bank_account: '',
    nominal_min: '',
    nominal_max: '',
    nominal_receipt_min: '',
    nominal_receipt_max: '',
    status: '',
    created_at_from: '',
    created_at_to: '',
    dateconfirmed_from: '',
    dateconfirmed_to: '',
    confirm_by: '',
};

export default function WithdrawList({
    totalWd,
    totalReceipt,
    totalTransfer,
    ewallet_balance,
    withdraws,
    wd_statuses,
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
                title: 'Withdraw',
                href: withdrawList(),
            },
        ],
    });

    const [confirmWd, setConfirmWd] = useState<Withdraw | null>(null);

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
        endpoint: withdrawList.url(),
        data: withdraws,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['withdraws'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    const adminColumnsStart: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Member',
            sortKey: 'username',
            filter: { type: 'input', key: 'username' },
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

    const memberDateColumn: SortableFilterableColumn<ColumnFilters> = {
        label: 'Tanggal',
        sortKey: 'created_at',
        filter: {
            type: 'date',
            fromKey: 'created_at_from',
            toKey: 'created_at_to',
        },
    };

    const commonColumnsMiddle: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Bank',
            sortKey: 'bank_name',
            filter: { type: 'input', key: 'bank_name' },
        },
        {
            label: 'Rekening Bank',
            sortKey: 'bank_account',
            filter: { type: 'input', key: 'bank_account' },
        },
        {
            label: 'Nominal',
            sortKey: 'nominal_receipt',
            filter: {
                type: 'range',
                minKey: 'nominal_receipt_min',
                maxKey: 'nominal_receipt_max',
            },
        },
        {
            label: 'Status',
            sortKey: 'status',
            filter: {
                type: 'select',
                key: 'status',
                options: wd_statuses ?? [],
            },
        },
        {
            label: 'Keterangan',
            sortKey: 'nominal',
            filter: {
                type: 'range',
                minKey: 'nominal_min',
                maxKey: 'nominal_max',
            },
        },
    ];

    const adminDateColumn: SortableFilterableColumn<ColumnFilters> = {
        label: 'Tanggal',
        sortKey: 'created_at',
        filter: {
            type: 'date',
            fromKey: 'created_at_from',
            toKey: 'created_at_to',
        },
    };

    const commonColumnsEnd: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Tanggal Konfirmasi',
            sortKey: 'dateconfirmed',
            filter: {
                type: 'date',
                fromKey: 'dateconfirmed_from',
                toKey: 'dateconfirmed_to',
            },
        },
        {
            label: 'Dikonfirmasi Oleh',
            sortKey: 'confirm_by',
            filter: { type: 'input', key: 'confirm_by' },
        },
    ];

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        ...(isAdmin ? adminColumnsStart : [memberDateColumn]),
        ...commonColumnsMiddle,
        ...(isAdmin ? [adminDateColumn] : []),
        ...commonColumnsEnd,
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
                                    {isAdmin
                                        ? 'Nilai Transfer'
                                        : 'Total Transferred'}
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
                    {isAdmin ? (
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
                    ) : (
                        <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                            <div>
                                <div className="flex justify-between gap-1">
                                    <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                        Saldo E-Wallet
                                    </span>
                                    <CreditCard className="h-4 w-4 text-blue-500" />
                                </div>
                                <div className="mt-2 mb-2 flex items-center">
                                    <span className="font-mono text-2xl font-extrabold">
                                        {formatCurrency(ewallet_balance)}
                                    </span>
                                </div>
                                <span className="block text-[11px] font-semibold tracking-wider text-muted-foreground">
                                    Total Saldo e-Wallet
                                </span>
                            </div>
                        </div>
                    )}
                </div>

                {/* Tab Menu */}
                {isAdmin && (
                    <div className="flex items-center gap-2">
                        <div className="inline-flex rounded-lg bg-muted p-1">
                            <Link
                                href={withdrawList()}
                                className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                            >
                                List Withdraw
                            </Link>
                            <Link
                                href={withdrawTotal()}
                                className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                            >
                                List Total Transaksi
                            </Link>
                        </div>
                    </div>
                )}

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
                                {isAdmin ? (
                                    <>
                                        {/* [MODE 1 - KHUSUS ADMIN]: Kolom Data Member & Tipe */}
                                        <td className="px-4 py-3">
                                            <div className="flex flex-col justify-start">
                                                <span className="font-medium text-foreground uppercase">
                                                    <div className="inline-flex justify-start whitespace-nowrap">
                                                        {row.username ?? '-'}
                                                    </div>
                                                </span>
                                                {row.name ? (
                                                    <span className="text-xs text-muted-foreground">
                                                        <div className="inline-flex justify-start whitespace-nowrap">
                                                            {row.name}
                                                        </div>
                                                    </span>
                                                ) : null}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                <EnumBadge
                                                    options={types}
                                                    value={row.type}
                                                />
                                            </div>
                                        </td>
                                    </>
                                ) : (
                                    /* [MODE 2 - KHUSUS MEMBER]: Kolom Data Tanggal */
                                    <td className="px-4 py-3 text-center text-xs whitespace-nowrap">
                                        {formatDate(row.created_at)}
                                    </td>
                                )}
                                <td className="px-4 py-3 text-center whitespace-nowrap">
                                    {row.bank_code ?? '-'} -{' '}
                                    {row.bank_name ?? '-'}
                                </td>
                                <td className="px-4 py-3 text-left">
                                    <div className="flex flex-col text-xs">
                                        <span className="font-mono text-muted-foreground">
                                            No.Rek : {row.bank_account ?? '-'}
                                        </span>
                                        {row.bank_account_name && (
                                            <span className="font-mono whitespace-nowrap text-muted-foreground">
                                                An.Rek :{' '}
                                                <span className="font-medium text-foreground uppercase">
                                                    {row.bank_account_name}
                                                </span>
                                            </span>
                                        )}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium whitespace-nowrap">
                                    {formatCurrency(row.nominal_receipt)}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <EnumBadge
                                        options={wd_statuses}
                                        value={row.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-left">
                                    <div className="flex flex-col text-xs whitespace-nowrap">
                                        <span className="font-mono text-muted-foreground">
                                            Withdraw :{' '}
                                            {formatCurrency(row.nominal)}
                                        </span>
                                        {row.admin_fund && (
                                            <span className="font-mono text-muted-foreground">
                                                Fee :{' '}
                                                <span className="font-medium text-foreground uppercase">
                                                    {formatCurrency(
                                                        row.admin_fund,
                                                    )}
                                                </span>
                                            </span>
                                        )}
                                    </div>
                                </td>
                                {isAdmin && (
                                    <td className="px-4 py-3 text-center text-xs whitespace-nowrap">
                                        {formatDate(row.created_at)}
                                    </td>
                                )}
                                <td className="px-4 py-3 text-center text-xs whitespace-nowrap">
                                    {formatDate(row.dateconfirmed)}
                                </td>
                                <td className="px-4 py-3 text-center text-xs">
                                    {row.confirm_by ?? '-'}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    {/* [KHUSUS ADMIN]: Tombol Aksi Konfirmasi Pencairan Dana */}
                                    {isAdmin && Number(row.status) === 0 ? (
                                        <button
                                            type="button"
                                            title="Konfirmasi"
                                            aria-label="Konfirmasi withdrawl"
                                            className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                            onClick={() => setConfirmWd(row)}
                                        >
                                            Transfer Manual
                                        </button>
                                    ) : (
                                        <span></span>
                                    )}
                                </td>
                            </>
                        )}
                        emptyIcon={DollarSign}
                    />
                </TableContainer>
            </div>

            <Dialog
                open={confirmWd !== null}
                onOpenChange={(open) => !open && setConfirmWd(null)}
            >
                <DialogContent>
                    <DialogHeader className="flex items-center">
                        <DialogTitle>Konfirmasi Withdraw</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin konfirmasi Withdrawl ini?
                        </DialogDescription>
                    </DialogHeader>

                    {confirmWd && (
                        <Form
                            action={confirm.url(confirmWd.id)}
                            method="put"
                            options={{ preserveScroll: true }}
                            onSuccess={() => setConfirmWd(null)}
                            resetOnSuccess={['password']}
                            className="space-y-4"
                        >
                            {({ processing, errors, resetAndClearErrors }) => (
                                <>
                                    {/* Rincian Transaksi */}
                                    <div className="space-y-2 rounded-lg border bg-muted/40 p-4 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Username:
                                            </span>
                                            <span className="font-medium uppercase">
                                                {confirmWd.username}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Nama:
                                            </span>
                                            <span className="font-medium">
                                                {confirmWd.name}
                                            </span>
                                        </div>
                                        <div className="border-t border-border"></div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Bank:
                                            </span>
                                            <span className="font-medium uppercase">
                                                {confirmWd.bank_code} -{' '}
                                                {confirmWd.bank_name}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                No. Rekening:
                                            </span>
                                            <span className="font-mono font-medium">
                                                {confirmWd.bank_account}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Atas Nama:
                                            </span>
                                            <span className="font-medium uppercase">
                                                {confirmWd.bank_account_name}
                                            </span>
                                        </div>
                                        <div className="border-t border-border"></div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Nominal Transfer:
                                            </span>
                                            <span className="font-mono font-bold text-red-500">
                                                {formatCurrency(
                                                    confirmWd.nominal_receipt,
                                                )}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Input Password */}
                                    <div className="space-y-1.5">
                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            required
                                            autoComplete="current-password"
                                            placeholder="Password Transaksi"
                                        />
                                        <InputError message={errors.password} />
                                    </div>

                                    {/* Tombol Aksi */}
                                    <DialogFooter className="gap-2 pt-2">
                                        <DialogClose asChild>
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                disabled={processing}
                                                onClick={() =>
                                                    resetAndClearErrors()
                                                }
                                            >
                                                Batal
                                            </Button>
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            variant="default"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Konfirmasi
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}
