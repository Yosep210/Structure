import {
    memberProduct,
    stockProduct,
    totalProduct,
    usageProduct,
} from '@/actions/App/Modules/Product/Http/Controllers/ProductController';
import {
    EnumBadge,
    type Option,
    SortableFilterableColumn,
    DataTable,
} from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/react';
import {
    ArrowLeftToLine,
    ChartNoAxesColumn,
    CreditCard,
    Home,
    ShoppingBag,
    ShoppingCart,
    WalletCards,
} from 'lucide-react';
import TableContainer from '@/components/table/paginates/table-container';

type Product = {
    id: number;
    id_pin?: number;
    stockist_username?: string | number;
    product_name?: string;
    varian_name?: string;
    status?: string | number;
    created_ad?: string;
    transfer_at?: string;
};

type ColumnFilters = {
    id_pin: string;
    stockist_username: string;
    product_name: string;
    varian_name: string;
    status: string;
    created_ad_from: string;
    created_ad_to: string;
    transfer_at_from: string;
    transfer_at_to: string;
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
    product?: Product[] | PaginatedData<Product>;
    targetMember?: targetMember | null;
    stockCount: number | string;
    usedCount: number | string;
    totalCount: number | string;
    statuses: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    id_pin: '',
    stockist_username: '',
    product_name: '',
    varian_name: '',
    status: '',
    created_ad_from: '',
    created_ad_to: '',
    transfer_at_from: '',
    transfer_at_to: '',
};

export default function ProductList({
    targetMember,
    stockCount,
    usedCount,
    totalCount,
    product,
    statuses,
    filters,
}: Props) {
    const { auth } = usePage<PageProps>().props;
    const isAdmin = auth?.user?.roles?.includes('admin') ?? false;
    const requestUsername = isAdmin
        ? (targetMember?.username ?? undefined)
        : undefined;

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
                title: 'Produk Stockist',
                href: memberProduct(requestUsername),
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
    } = useTableQuery<Product, ColumnFilters>({
        endpoint: memberProduct.url(requestUsername),
        data: product,
        filters,
        defaultSortKey: 'created_ad',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['product', 'filters'],
    });

    const handleBack = () => {
        router.visit(stockProduct());
    };

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'ID Produk',
            sortKey: 'id_pin',
            filter: { type: 'input', key: 'id_pin' },
        },
        {
            label: 'Stockist',
            sortKey: 'stockist_username',
            filter: { type: 'input', key: 'stockist_username' },
        },
        {
            label: 'Produk',
            sortKey: 'product_name',
            filter: { type: 'input', key: 'product_name' },
        },
        {
            label: 'Varian',
            sortKey: 'varian_name',
            filter: { type: 'input', key: 'varian_name' },
        },
        {
            label: 'Keterangan',
            sortKey: 'status',
            filter: { type: 'select', key: 'status', options: statuses ?? [] },
        },
        {
            label: 'Dibuat',
            sortKey: 'created_ad',
            filter: {
                type: 'date',
                fromKey: 'created_ad_from',
                toKey: 'created_ad_to',
            },
        },
        {
            label: 'Dikirim',
            sortKey: 'transfer_at',
            filter: {
                type: 'date',
                fromKey: 'transfer_at_from',
                toKey: 'transfer_at_to',
            },
        },
    ];

    return (
        <>
            <Head title="Data Produk" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {'Produk Stockist'}
                            </h1>
                        </div>
                    </div>
                </div>

                {/* [MODE 2 - KHUSUS ADMIN DETAIL MEMBER]: Info Bar Member & Tombol Kembali */}
                {targetMember && isAdmin && (
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold tracking-tight text-foreground">
                                Member : {targetMember?.name} (
                                {targetMember?.username})
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
                                Total Stok
                            </span>
                            <div className="mt-2 flex items-center justify-between gap-1">
                                <span className="font-mono text-2xl font-extrabold">
                                    {totalCount}
                                </span>
                                <CreditCard className="h-4 w-4 text-green-500" />
                            </div>
                        </div>
                    </div>
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                Stok Produk
                            </span>
                            <div className="mt-2 flex items-center justify-between gap-1">
                                <span className="font-mono text-2xl font-extrabold">
                                    {stockCount}
                                </span>
                                <CreditCard className="h-4 w-4 text-blue-500" />
                            </div>
                        </div>
                    </div>
                    <div className="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition hover:shadow-md">
                        <div>
                            <span className="block text-[11px] font-semibold tracking-wider uppercase">
                                Penggunaan Produk
                            </span>
                            <div className="mt-2 flex items-center justify-between gap-1">
                                <span className="font-mono text-2xl font-extrabold">
                                    {usedCount}
                                </span>
                                <CreditCard className="h-4 w-4 text-red-500" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Tab Menu */}
                <div className="flex items-center gap-2">
                    <div className="inline-flex rounded-lg bg-muted p-1">
                        <Link
                            href={memberProduct.url(requestUsername)}
                            className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                        >
                            <ChartNoAxesColumn className="h-4 w-4" />
                            List Produk
                        </Link>
                        <Link
                            href={usageProduct.url(requestUsername)}
                            className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                        >
                            <WalletCards className="h-4 w-4" />
                            Penggunaan Produk
                        </Link>
                        <Link
                            href={totalProduct.url(requestUsername)}
                            className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                        >
                            <ShoppingBag className="h-4 w-4" />
                            Total Produk
                        </Link>
                    </div>
                </div>

                <TableContainer
                    data={
                        product && !Array.isArray(product) ? product : undefined
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
                                <td className="px-4 py-3 text-center font-medium text-foreground uppercase">
                                    {row.id_pin ?? '-'}
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.stockist_username}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {row.product_name ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.varian_name ?? '-'}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        <EnumBadge
                                            options={statuses}
                                            value={row.status}
                                        />
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {formatDate(row.created_ad)}
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {formatDate(row.transfer_at)}
                                </td>
                                <td className="px-4 py-3"></td>
                            </>
                        )}
                        emptyIcon={ShoppingCart}
                    />
                </TableContainer>
            </div>
        </>
    );
}
