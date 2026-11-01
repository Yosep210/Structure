import {
    EnumBadge,
    type Option,
    SortableFilterableColumn,
    DataTable,
} from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, Link, setLayoutProps } from '@inertiajs/react';
import {
    Check,
    Clock,
    Home,
    SendHorizonal,
    ShoppingCart,
    X,
} from 'lucide-react';
import { formatCurrency, formatDate } from '@/lib/format';
import { orderToCompany } from '@/actions/App/Modules/Product/Http/Controllers/ProductController';
import TableContainer from '@/components/table/paginates/table-container';

type OrderDetail = {
    id: number;
    qty: number;
    product?: { name: string } | null;
    package?: { name: string } | null;
    varian?: { varian: string } | null;
};

type Order = {
    id: number;
    invoice: string;
    member_name: string;
    member_username: string;
    type_order: string;
    details: OrderDetail[];
    subtotal: number | string;
    unique: number | null;
    total_checkout: number | string;
    payment_method: string | null;
    resi: string | null;
    created_at: string;
    updated_at: string | null;
    dateconfirmed: string | null;
    dateexpired: string | null;
    confirmed_by: string | null;
    modified_by: string | null;
};

type OrderTab = 'pending' | 'confirm' | 'done' | 'cancel';

type ColumnFilters = {
    invoice: string;
    name: string;
    type_order: string;
    product_name: string;
    confirmed_by: string;
    payment_method: string;
    total_checkout_min: string;
    total_checkout_max: string;
    resi: string;
    created_at_from: string;
    created_at_to: string;
    cancelled_at_from: string;
    cancelled_at_to: string;
    cancelled_by: string;
    dateconfirmed_from: string;
    dateconfirmed_to: string;
    dateexpired_from: string;
    dateexpired_to: string;
    updated_at_from: string;
    updated_at_to: string;
};

type Props = {
    order?: Order[] | PaginatedData<Order>;
    tab: OrderTab;
    statuses: Option[];
    payment?: Option[];
    types?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    invoice: '',
    name: '',
    type_order: '',
    product_name: '',
    confirmed_by: '',
    payment_method: '',
    total_checkout_min: '',
    total_checkout_max: '',
    resi: '',
    created_at_from: '',
    created_at_to: '',
    cancelled_at_from: '',
    cancelled_at_to: '',
    cancelled_by: '',
    dateconfirmed_from: '',
    dateconfirmed_to: '',
    dateexpired_from: '',
    dateexpired_to: '',
    updated_at_from: '',
    updated_at_to: '',
};

export default function OrderCompanyList({
    order,
    tab,
    payment,
    types,
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
                title: 'Pemesanan Produk',
                href: orderToCompany(),
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
    } = useTableQuery<Order, ColumnFilters>({
        endpoint: orderToCompany.url({ query: { tab } }),
        data: order,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['order', 'filters'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;
    const isPending = tab === 'pending';
    const isConfirmed = tab === 'confirm';
    const isCancelled = tab === 'cancel';
    const hasConfirmationDetails = tab === 'confirm' || tab === 'done';

    const pending: SortableFilterableColumn<ColumnFilters> = {
        label: 'Tanggal Expired',
        sortKey: 'dateexpired',
        filter: {
            type: 'date',
            fromKey: 'dateexpired_from',
            toKey: 'dateexpired_to',
        },
    };

    const confirmDetail: SortableFilterableColumn<ColumnFilters> = {
        label: 'Tanggal Konfirmasi',
        sortKey: 'dateconfirmed',
        filter: {
            type: 'date',
            fromKey: 'dateconfirmed_from',
            toKey: 'dateconfirmed_to',
        },
    };

    const cancelled: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Tanggal dibatalkan',
            sortKey: 'updated_at',
            filter: {
                type: 'date',
                fromKey: 'updated_at_from',
                toKey: 'updated_at_to',
            },
        },
        {
            label: 'Dibatalkan Oleh',
            sortKey: 'cancelled_by',
            filter: { type: 'input', key: 'cancelled_by' },
        },
    ];

    const confirmedBy: SortableFilterableColumn<ColumnFilters> = {
        label: 'Dikonfirmasi Oleh',
        sortKey: 'confirmed_by',
        filter: { type: 'input', key: 'confirmed_by' },
    };

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Invoice',
            sortKey: 'invoice',
            filter: { type: 'input', key: 'invoice' },
        },
        {
            label: 'Member',
            sortKey: 'name',
            filter: { type: 'input', key: 'name' },
        },
        {
            label: 'Tipe',
            sortKey: 'type_order',
            filter: {
                type: 'select',
                key: 'type_order',
                options: types ?? [],
            },
        },
        {
            label: 'Produk',
            sortKey: 'product_name',
            filter: { type: 'input', key: 'product_name' },
        },
        {
            label: 'Total Omzet',
            sortKey: 'total_checkout',
            filter: {
                type: 'range',
                minKey: 'total_checkout_min',
                maxKey: 'total_checkout_max',
            },
        },
        {
            label: 'Resi',
            sortKey: 'resi',
            filter: { type: 'input', key: 'resi' },
        },
        {
            label: 'Tipe Pembayaran',
            sortKey: 'payment_method',
            filter: {
                type: 'select',
                key: 'payment_method',
                options: payment ?? [],
            },
        },
        {
            label: 'Tanggal',
            sortKey: 'created_at',
            filter: {
                type: 'date',
                fromKey: 'created_at_from',
                toKey: 'created_at_to',
            },
        },
        ...(isPending ? [pending] : []),
        ...(hasConfirmationDetails ? [confirmDetail, confirmedBy] : []),
        ...(isCancelled ? cancelled : []),
    ];

    return (
        <>
            <Head title="Pemesanan Produk" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {'Pemesanan Produk'}
                            </h1>
                        </div>
                    </div>
                </div>

                {/* Tab Menu */}
                <div className="flex items-center gap-2">
                    <div className="inline-flex rounded-lg bg-muted p-1">
                        <Link
                            href={orderToCompany.url({
                                query: { tab: 'pending' },
                            })}
                            preserveState={false}
                            aria-current={isPending ? 'page' : undefined}
                            className={`inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-all ${
                                isPending
                                    ? 'bg-background text-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Clock className="h-4 w-4" />
                            New (Pending)
                        </Link>
                        <Link
                            href={orderToCompany.url({
                                query: { tab: 'confirm' },
                            })}
                            preserveState={false}
                            aria-current={
                                tab === 'confirm' ? 'page' : undefined
                            }
                            className={`inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-all ${
                                tab === 'confirm'
                                    ? 'bg-background text-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Check className="h-4 w-4" />
                            Confirmed
                        </Link>
                        <Link
                            href={orderToCompany.url({
                                query: { tab: 'done' },
                            })}
                            preserveState={false}
                            aria-current={tab === 'done' ? 'page' : undefined}
                            className={`inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-all ${
                                tab === 'done'
                                    ? 'bg-background text-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <SendHorizonal className="h-4 w-4" />
                            Done
                        </Link>
                        <Link
                            href={orderToCompany.url({
                                query: { tab: 'cancel' },
                            })}
                            preserveState={false}
                            aria-current={isCancelled ? 'page' : undefined}
                            className={`inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-all ${
                                isCancelled
                                    ? 'bg-background text-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <X className="h-4 w-4" />
                            Cancelled
                        </Link>
                    </div>
                </div>

                <TableContainer
                    data={order && !Array.isArray(order) ? order : undefined}
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
                        renderRow={(row) => {
                            const expirationTime = row.dateexpired
                                ? Date.parse(row.dateexpired)
                                : Number.NaN;
                            const hasValidExpiration =
                                Number.isFinite(expirationTime);
                            const isExpired =
                                hasValidExpiration &&
                                expirationTime <= Date.now();
                            const canShowExpired =
                                isPending &&
                                row.dateconfirmed === null &&
                                isExpired;
                            const canShowPendingActions =
                                isPending &&
                                row.dateconfirmed === null &&
                                !isExpired;

                            return (
                                <>
                                    <td className="px-4 py-3 text-center font-medium">
                                        {row.invoice}
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <div className="flex flex-col items-center justify-center">
                                            <span className="font-medium text-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.member_username}
                                                </div>
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.member_name}
                                                </div>
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <div className="inline-flex justify-start whitespace-nowrap">
                                            <EnumBadge
                                                options={types}
                                                value={row.type_order}
                                            />
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex min-w-40 flex-col gap-1">
                                            {row.details.length > 0 ? (
                                                row.details.map((detail) => {
                                                    const name =
                                                        detail.product?.name ??
                                                        detail.package?.name ??
                                                        'Produk';

                                                    return (
                                                        <span
                                                            key={detail.id}
                                                            className="whitespace-nowrap"
                                                        >
                                                            {name}
                                                            {detail.varian
                                                                ?.varian
                                                                ? ` (${detail.varian.varian})`
                                                                : ''}{' '}
                                                            [{detail.qty} qty]
                                                        </span>
                                                    );
                                                })
                                            ) : (
                                                <span>-</span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="min-w-52 whitespace-nowrap">
                                            <div>
                                                Subtotal :{' '}
                                                {formatCurrency(row.subtotal)}
                                            </div>
                                            <div>
                                                Kode Unik : {row.unique ?? '-'}
                                            </div>
                                            <div className="my-1 border-t border-border" />
                                            <div className="font-semibold">
                                                Total Bayar : Rp{' '}
                                                {formatCurrency(
                                                    row.total_checkout,
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="inline-flex justify-center whitespace-nowrap">
                                            {row.resi ?? '-'}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <div className="inline-flex justify-start whitespace-nowrap">
                                            <EnumBadge
                                                options={payment}
                                                value={row.payment_method}
                                            />
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-center font-medium">
                                        {formatDate(row.created_at)}
                                    </td>
                                    <td className="px-4 py-3 text-center font-medium">
                                        {isPending
                                            ? formatDate(row.dateexpired)
                                            : hasConfirmationDetails
                                              ? formatDate(row.dateconfirmed)
                                              : formatDate(row.updated_at)}
                                    </td>
                                    {!isPending && (
                                        <td className="px-4 py-3 text-center font-medium">
                                            {isCancelled
                                                ? (row.modified_by ?? '-')
                                                : (row.confirmed_by ?? '-')}
                                        </td>
                                    )}
                                    <td className="px-4 py-3">
                                        <div className="flex items-center justify-center gap-1.5">
                                            {canShowExpired ? (
                                                <button
                                                    type="button"
                                                    title="Pesanan sudah expired"
                                                    aria-label="Pesanan sudah expired"
                                                    className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                                >
                                                    <Clock className="h-3.5 w-3.5 text-red-500" />
                                                </button>
                                            ) : canShowPendingActions ? (
                                                <>
                                                    <button
                                                        type="button"
                                                        title="Konfirmasi pesanan"
                                                        aria-label="Konfirmasi pesanan"
                                                        className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                                    >
                                                        <Check className="h-3.5 w-3.5 text-green-500" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        title="Batalkan pesanan"
                                                        aria-label="Batalkan pesanan"
                                                        className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                                    >
                                                        <X className="h-3.5 w-3.5 text-red-500" />
                                                    </button>
                                                </>
                                            ) : isConfirmed ? (
                                                <button
                                                    type="button"
                                                    title="Kirim pesanan"
                                                    aria-label="Kirim pesanan"
                                                    className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                                >
                                                    <SendHorizonal className="h-3.5 w-3.5 text-blue-500" />
                                                </button>
                                            ) : (
                                                '-'
                                            )}
                                        </div>
                                    </td>
                                </>
                            );
                        }}
                        emptyIcon={ShoppingCart}
                    />
                </TableContainer>
            </div>
        </>
    );
}
