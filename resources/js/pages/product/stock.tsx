import { SortableFilterableColumn, DataTable } from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, Link, router, setLayoutProps } from '@inertiajs/react';
import {
    ChartNoAxesColumn,
    Eye,
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
import TableContainer from '@/components/table/paginates/table-container';

type Stockist = {
    id: number;
    username?: string;
    name?: string;
    total_products?: string | number;
    stock_products?: string | number;
};

type ColumnFilters = {
    username: string;
    name: string;
    total_products_min: string;
    total_products_max: string;
    stock_products_min: string;
    stock_products_max: string;
};

type Props = {
    stockist?: Stockist[] | PaginatedData<Stockist>;
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    username: '',
    name: '',
    total_products_min: '',
    total_products_max: '',
    stock_products_min: '',
    stock_products_max: '',
};

export default function StockistList({ stockist, filters }: Props) {
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
                href: stockProduct(),
            },
        ],
    });

    const handleDetail = (username: string) => {
        router.visit(memberProduct.url(username));
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
    } = useTableQuery<Stockist, ColumnFilters>({
        endpoint: stockProduct.url(),
        data: stockist,
        filters,
        defaultSortKey: 'total_active',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['stockist', 'filters'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

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
            label: 'Total Product',
            sortKey: 'total_products',
            filter: {
                type: 'range',
                minKey: 'total_products_min',
                maxKey: 'total_products_max',
            },
        },
        {
            label: 'Stock Product',
            sortKey: 'stock_products',
            filter: {
                type: 'range',
                minKey: 'stock_products_min',
                maxKey: 'stock_products_max',
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

                {/* Tab Menu */}
                <div className="flex items-center gap-2">
                    <div className="inline-flex rounded-lg bg-muted p-1">
                        <Link
                            href={stockProduct.url()}
                            className="inline-flex items-center gap-2 rounded-md bg-background px-3.5 py-1.5 text-sm font-medium text-foreground shadow-xs transition-all"
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
                            className="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:text-foreground"
                        >
                            <ShoppingBag className="h-4 w-4" />
                            Total Produk
                        </Link>
                    </div>
                </div>

                <TableContainer
                    data={
                        stockist && !Array.isArray(stockist)
                            ? stockist
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
                                <td className="px-4 py-3 text-center font-medium text-foreground uppercase">
                                    {row.username ?? '-'}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {row.name ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.total_products}
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.stock_products}
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
                        emptyIcon={ShoppingCart}
                    />
                </TableContainer>
            </div>
        </>
    );
}
