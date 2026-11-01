import { SortableFilterableColumn, DataTable } from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
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
import {
    memberProduct,
    stockProduct,
    totalProduct,
    usageProduct,
} from '@/actions/App/Modules/Product/Http/Controllers/ProductController';
import { formatCurrency } from '@/lib/format';
import TableContainer from '@/components/table/paginates/table-container';

type Total = {
    id: number;
    product_name?: string;
    varian_name?: string;
    price?: string | number;
    stock_in?: string | number;
    total_stock?: string | number;
    stock_out?: string | number;
};

type ColumnFilters = {
    product_name: string;
    varian_name: string;
    price_min: string;
    price_max: string;
    stock_in_min: string;
    stock_in_max: string;
    total_stock_min: string;
    total_stock_max: string;
    stock_out_min: string;
    stock_out_max: string;
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
    total?: Total[] | PaginatedData<Total>;
    targetMember?: targetMember | null;
    stockCount: number | string;
    usedCount: number | string;
    totalCount: number | string;
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    product_name: '',
    varian_name: '',
    price_min: '',
    price_max: '',
    stock_in_min: '',
    stock_in_max: '',
    total_stock_min: '',
    total_stock_max: '',
    stock_out_min: '',
    stock_out_max: '',
};

export default function TotalList({
    targetMember,
    stockCount,
    usedCount,
    totalCount,
    total,
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
                href: totalProduct(requestUsername),
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
    } = useTableQuery<Total, ColumnFilters>({
        endpoint: totalProduct.url(requestUsername),
        data: total,
        filters,
        defaultSortKey: 'total_stock',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['total', 'filters'],
    });

    const handleBack = () => {
        router.visit(stockProduct());
    };

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    const global: SortableFilterableColumn<ColumnFilters> = {
        label: 'Harga',
        sortKey: 'price',
        filter: {
            type: 'range',
            minKey: 'price_min',
            maxKey: 'price_max',
        },
    };

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
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
        ...(isAdmin && !targetMember ? [global] : []),
        {
            label: 'Total Product',
            sortKey: 'stock_in',
            filter: {
                type: 'range',
                minKey: 'stock_in_min',
                maxKey: 'stock_in_max',
            },
        },
        {
            label: 'Penggunaan Product',
            sortKey: 'stock_out',
            filter: {
                type: 'range',
                minKey: 'stock_out_min',
                maxKey: 'stock_out_max',
            },
        },
        {
            label: 'Stock Product',
            sortKey: 'total_stock',
            filter: {
                type: 'range',
                minKey: 'total_stock_min',
                maxKey: 'total_stock_max',
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

                {(!isAdmin || requestUsername) && (
                    <>
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
                                    className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
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
                                    className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                                >
                                    <ShoppingBag className="h-4 w-4" />
                                    Total Produk
                                </Link>
                            </div>
                        </div>
                    </>
                )}

                {/* Tab Menu */}
                {!targetMember && (
                    <div className="flex items-center gap-2">
                        <div className="inline-flex rounded-lg bg-muted p-1">
                            <Link
                                href={stockProduct.url()}
                                className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                            >
                                <ChartNoAxesColumn className="h-4 w-4" />
                                Stok Produk Stockist
                            </Link>
                            <Link
                                href={usageProduct.url()}
                                className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                            >
                                <WalletCards className="h-4 w-4" />
                                Penggunaan Produk
                            </Link>
                            <Link
                                href={totalProduct.url()}
                                className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
                            >
                                <ShoppingBag className="h-4 w-4" />
                                Total Produk
                            </Link>
                        </div>
                    </div>
                )}

                <TableContainer
                    data={total && !Array.isArray(total) ? total : undefined}
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
                                        {row.product_name ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium text-foreground uppercase">
                                    {row.varian_name ?? '-'}
                                </td>
                                {isAdmin && !targetMember && (
                                    <td className="px-4 py-3 text-center font-medium">
                                        {formatCurrency(row.price)}
                                    </td>
                                )}
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.stock_in}
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.stock_out}
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.total_stock}
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
