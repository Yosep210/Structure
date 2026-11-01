import {
    DataTable,
    EnumBadge,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { formatCurrency, formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, setLayoutProps } from '@inertiajs/react';
import { Home, ShoppingCart } from 'lucide-react';
import { historyRo } from '@/actions/App/Modules/Product/Http/Controllers/ProductController';
import TableContainer from '@/components/table/paginates/table-container';

type ProductLine = {
    name: string;
    qty: number;
};

type RO = {
    id: number;
    created_at: string;
    invoice: string;
    activator_username: string | null;
    activator_name: string | null;
    member_username: string;
    member_name: string;
    product_list: ProductLine[];
    subtotal: number | string;
    unique: number | null;
    total_checkout: number | string;
    payment_method: string | null;
    status: string | number;
    dateconfirmed: string | null;
};

type ColumnFilters = {
    created_at_from: string;
    created_at_to: string;
    invoice: string;
    created_by: string;
    username: string;
    product_list: string;
    total_checkout_min: string;
    total_checkout_max: string;
    payment_method: string;
    status: string;
    dateconfirmed_from: string;
    dateconfirmed_to: string;
};

type Props = {
    ro?: RO[] | PaginatedData<RO>;
    statuses?: Option[];
    payment?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    created_at_from: '',
    created_at_to: '',
    invoice: '',
    created_by: '',
    username: '',
    product_list: '',
    total_checkout_min: '',
    total_checkout_max: '',
    payment_method: '',
    status: '',
    dateconfirmed_from: '',
    dateconfirmed_to: '',
};

export default function HistoryRo({ ro, statuses, payment, filters }: Props) {
    setLayoutProps({
        breadcrumbs: [
            { icon: Home, href: dashboard() },
            { title: 'Laporan', href: '#' },
            { title: 'History RO', href: historyRo() },
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
    } = useTableQuery<RO, ColumnFilters>({
        endpoint: historyRo.url(),
        data: ro,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['ro', 'filters'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

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
        {
            label: 'Invoice',
            sortKey: 'invoice',
            filter: { type: 'input', key: 'invoice' },
        },
        {
            label: 'Aktivator',
            sortKey: 'created_by',
            filter: { type: 'input', key: 'created_by' },
        },
        {
            label: 'Member',
            sortKey: 'username',
            filter: { type: 'input', key: 'username' },
        },
        {
            label: 'Produk',
            sortKey: 'product_list',
            filter: { type: 'input', key: 'product_list' },
        },
        {
            label: 'Total Pembayaran',
            sortKey: 'total_checkout',
            filter: {
                type: 'range',
                minKey: 'total_checkout_min',
                maxKey: 'total_checkout_max',
            },
        },
        {
            label: 'Metode Pembayaran',
            sortKey: 'payment_method',
            filter: {
                type: 'select',
                key: 'payment_method',
                options: payment ?? [],
            },
        },
        {
            label: 'Status',
            sortKey: 'status',
            filter: { type: 'select', key: 'status', options: statuses ?? [] },
        },
        {
            label: 'Tanggal Konfirmasi',
            sortKey: 'dateconfirmed',
            filter: {
                type: 'date',
                fromKey: 'dateconfirmed_from',
                toKey: 'dateconfirmed_to',
            },
        },
    ];

    return (
        <>
            <Head title="History RO" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {'History RO'}
                            </h1>
                        </div>
                    </div>
                </div>

                <TableContainer
                    data={ro && !Array.isArray(ro) ? ro : undefined}
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
                                    {formatDate(row.created_at)}
                                </td>
                                <td className="px-4 py-3 text-center font-medium text-foreground">
                                    {row.invoice}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex flex-col items-center justify-center">
                                        <span className="font-medium text-foreground">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.activator_username ?? '-'}
                                            </div>
                                        </span>
                                        {row.activator_name ? (
                                            <span className="text-xs text-muted-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.activator_name}
                                                </div>
                                            </span>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex flex-col items-center justify-center">
                                        <span className="font-medium text-foreground">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.member_username ?? '-'}
                                            </div>
                                        </span>
                                        {row.member_name ? (
                                            <span className="text-xs text-muted-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.member_name}
                                                </div>
                                            </span>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex min-w-40 flex-col gap-1">
                                        {row.product_list.length
                                            ? row.product_list
                                                  .map(
                                                      (product) =>
                                                          `${product.name} (qty: ${product.qty})`,
                                                  )
                                                  .join(', ')
                                            : '-'}
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
                                            {formatCurrency(row.total_checkout)}
                                        </div>
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <EnumBadge
                                        options={payment}
                                        value={row.payment_method}
                                    />
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <EnumBadge
                                        options={statuses}
                                        value={row.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-center whitespace-nowrap">
                                    {row.dateconfirmed
                                        ? formatDate(row.dateconfirmed)
                                        : '-'}
                                </td>
                                <td className="px-4 py-3 text-center"></td>
                            </>
                        )}
                        emptyIcon={ShoppingCart}
                    />
                </TableContainer>
            </div>
        </>
    );
}
