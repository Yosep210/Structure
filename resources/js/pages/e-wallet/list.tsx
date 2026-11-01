import {
    DataTable,
    EnumBadge,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { Head, router, setLayoutProps, usePage } from '@inertiajs/react';
import {
    ArrowLeftToLine,
    CreditCard,
    DollarSign,
    Eye,
    Home,
} from 'lucide-react';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { useState } from 'react';
import { eWalletList } from '@/actions/App/Modules/EWallet/Http/Controllers/EWalletController';
import { formatCurrency, formatDate } from '@/lib/format';
import TableContainer from '@/components/table/paginates/table-container';

type Wallet = {
    id: number;
    username?: string;
    name?: string;
    wd_status?: number | string | null;
    wd_min?: number | string | null;
    ewallet_balance?: number | string;
    created_at: string;
    source: string;
    type: string;
    amount: string | number | null;
    description: string;
    nominal: string | number | null;
    tax: string | number | null;
    amount_tax: string | number | null;
};

type ColumnFilters = {
    username: string;
    name: string;
    wd_status: string;
    ewallet_balance_min: string;
    ewallet_balance_max: string;
    created_at_from: string;
    created_at_to: string;
    source: string;
    type: string;
    amount_min: string;
    amount_max: string;
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
    targetMember?: targetMember | null;
    wallet?: Wallet[] | PaginatedData<Wallet>;
    depositeIn: number | string;
    depositeOut: number | string;
    depositeSaldo: number | string;
    depositeManual: number | string;
    depositeAuto: number | string;
    wd_statuses?: Option[];
    sources?: Option[];
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
    wd_status: '',
    ewallet_balance_min: '',
    ewallet_balance_max: '',
    created_at_from: '',
    created_at_to: '',
    source: '',
    type: '',
    amount_max: '',
    amount_min: '',
    description: '',
};

// function formatCurrency(val?: number | string | null): string {
//     if (val === undefined || val === null || val === '') return '-';
//     const num = Number(val);
//     if (isNaN(num)) return String(val);
//     return new Intl.NumberFormat('id-ID', {
//         style: 'currency',
//         currency: 'IDR',
//         minimumFractionDigits: 0,
//         maximumFractionDigits: 2,
//     }).format(num);
// }

// const formatDate = (value?: string | number | Date | null): string => {
//     const raw = value ? String(value) : '';
//     if (!raw) return '-';
//     const date = new Date(raw);
//     if (Number.isNaN(date.getTime())) return raw;

//     const day = String(date.getUTCDate()).padStart(2, '0');
//     const month = date.toLocaleString('en', {
//         month: 'short',
//         timeZone: 'UTC',
//     });
//     const year = String(date.getUTCFullYear()).slice(-2);
//     return `${day} ${month} ${year}`;
// };

export default function WalletList({
    wallet,
    targetMember,
    depositeIn,
    depositeOut,
    depositeSaldo,
    depositeManual,
    depositeAuto,
    wd_statuses,
    sources,
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
                title: 'eWallet',
                href: eWalletList(),
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
    } = useTableQuery<Wallet, ColumnFilters>({
        endpoint: eWalletList.url(targetMember?.username ?? undefined),
        data: wallet,
        filters,
        defaultSortKey: targetMember ? 'created_at' : 'ewallet_balance',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['wallet'],
    });

    const [isDetailOpen, setIsDetailOpen] = useState(false);
    const memberName = targetMember?.name ?? '-';
    const memberUsername = targetMember?.username ?? '-';

    const handleBack = () => {
        router.visit(eWalletList());
    };

    const handleDetail = (username: string) => {
        router.visit(eWalletList(username));
    };

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
            sortKey: 'wd_status',
            filter: {
                type: 'select',
                key: 'wd_status',
                options: wd_statuses ?? [],
            },
        },
        {
            label: 'Jumlah',
            sortKey: 'ewallet_balance',
            filter: {
                type: 'range',
                minKey: 'ewallet_balance_min',
                maxKey: 'ewallet_balance_max',
            },
        },
    ];

    const list: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Tanggal',
            sortKey: 'created_at',
            filter: {
                type: 'date',
                fromKey: 'created_at_from',
                toKey: 'created_at_to',
            },
        },
        {
            label: 'Tipe',
            sortKey: 'source',
            filter: {
                type: 'select',
                key: 'source',
                options: sources ?? [],
            },
        },
        {
            label: 'Status',
            sortKey: 'type',
            filter: {
                type: 'select',
                key: 'type',
                options: types ?? [],
            },
        },
        {
            label: 'Nominal',
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

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        ...(!targetMember ? global : []),
        ...(targetMember ? list : []),
    ];

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    return (
        <>
            <Head title="eWallet" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                eWallet Commission
                            </h1>
                        </div>
                    </div>
                </div>

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

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                Total Saldo eWallet
                            </span>
                            <div className="mt-2 flex items-center justify-between gap-1">
                                <span className="font-mono text-2xl font-extrabold">
                                    {formatCurrency(depositeSaldo)}
                                </span>
                                <CreditCard className="h-4 w-4 text-green-500" />
                            </div>
                        </div>
                        {!targetMember && isAdmin && (
                            <div className="mt-4 flex items-center justify-between border-t pt-3">
                                <button
                                    type="button"
                                    aria-label="Detail Saldo eWallet"
                                    className="flex cursor-pointer items-center gap-1 border-none p-0 text-xs font-semibold text-blue-600 transition hover:text-blue-700"
                                    onClick={() => setIsDetailOpen(true)}
                                >
                                    Detail &rarr;
                                </button>
                            </div>
                        )}
                    </div>
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                Total IN (eWallet)
                            </span>
                            <div className="mt-2 flex items-center justify-between gap-1">
                                <span className="font-mono text-2xl font-extrabold">
                                    {formatCurrency(depositeIn)}
                                </span>
                                <CreditCard className="h-4 w-4 text-blue-500" />
                            </div>
                        </div>
                    </div>
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                Total OUT (eWallet)
                            </span>
                            <div className="mt-2 flex items-center justify-between gap-1">
                                <span className="font-mono text-2xl font-extrabold">
                                    {formatCurrency(depositeOut)}
                                </span>
                                <CreditCard className="h-4 w-4 text-red-500" />
                            </div>
                        </div>
                    </div>
                </div>

                <TableContainer
                    data={wallet && !Array.isArray(wallet) ? wallet : undefined}
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
                                {!targetMember && (
                                    <>
                                        {/* [MODE 1 - KHUSUS ADMIN GLOBAL]: Kolom Data Saldo Seluruh Member */}
                                        <td className="px-4 py-3 text-center font-medium text-foreground uppercase">
                                            {row.username ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.name ?? '-'}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <div className="flex flex-col items-center justify-center gap-1">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    <EnumBadge
                                                        options={wd_statuses}
                                                        value={row.wd_status}
                                                    />
                                                </div>
                                                {row.wd_min ? (
                                                    <span className="text-xs text-muted-foreground">
                                                        <div className="inline-flex justify-start whitespace-nowrap">
                                                            Minimal {' : '}
                                                            {formatCurrency(
                                                                row.wd_min,
                                                            )}
                                                        </div>
                                                    </span>
                                                ) : null}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-center font-medium">
                                            {formatCurrency(
                                                row.ewallet_balance,
                                            )}
                                        </td>
                                    </>
                                )}
                                {targetMember && (
                                    <>
                                        {/* [MODE 2 - KHUSUS MEMBER & ADMIN DETAIL MEMBER]: Kolom Data Mutasi Saldo */}
                                        <td className="px-4 py-3 text-center">
                                            {formatDate(row.created_at)}
                                        </td>
                                        <td className="px-4 py-3 text-center font-medium">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                <EnumBadge
                                                    options={sources}
                                                    value={row.source}
                                                />
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-center font-medium">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                <EnumBadge
                                                    options={types}
                                                    value={row.type}
                                                />
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-center font-medium">
                                            {formatCurrency(row.amount)}
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
                                                            {formatCurrency(
                                                                row.nominal,
                                                            )}
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
                                                                        row.amount ??
                                                                            0,
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
                                    </>
                                )}
                                <td className="px-4 py-3">
                                    <div className="flex items-center justify-center gap-1.5">
                                        {!targetMember && (
                                            <button
                                                type="button"
                                                title="Detail"
                                                aria-label="Detail eWallet Member"
                                                className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
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

            <Dialog open={isDetailOpen} onOpenChange={setIsDetailOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Detail Saldo eWallet</DialogTitle>
                        <DialogDescription>
                            Rincian total saldo eWallet berdasarkan status
                            withdrawal (WD).
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-3 py-2 text-sm">
                        <div className="flex items-center justify-between rounded-lg border p-3">
                            <span className="font-medium text-muted-foreground">
                                eWallet Auto WD
                            </span>
                            <span className="font-mono font-bold text-foreground">
                                {formatCurrency(depositeAuto)}
                            </span>
                        </div>
                        <div className="flex items-center justify-between rounded-lg border p-3">
                            <span className="font-medium text-muted-foreground">
                                eWallet Manual WD
                            </span>
                            <span className="font-mono font-bold text-foreground">
                                {formatCurrency(depositeManual)}
                            </span>
                        </div>
                        <div className="my-1 border-t border-border" />
                        <div className="flex items-center justify-between rounded-lg bg-muted/40 p-3">
                            <span className="font-semibold text-foreground">
                                Total Saldo eWallet
                            </span>
                            <span className="font-mono font-extrabold text-green-500">
                                {formatCurrency(depositeSaldo)}
                            </span>
                        </div>
                    </div>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline">Tutup</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
