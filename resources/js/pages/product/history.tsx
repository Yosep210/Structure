import {
    EnumBadge,
    type Option,
    DataTable,
    SortableFilterableColumn,
} from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { dashboard } from '@/routes';
import { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, setLayoutProps } from '@inertiajs/react';
import { Home, ShoppingCart } from 'lucide-react';
import { formatDate } from '@/lib/format';
import { historySendProduct } from '@/actions/App/Modules/Product/Http/Controllers/ProductController';
import TableContainer from '@/components/table/paginates/table-container';

type History = {
    id: number;
    sender_username?: number;
    recipient_username?: string;
    product_name?: string;
    qty?: number | string;
    type?: string | number;
    description?: string;
    created_at?: string;
};

type ColumnFilters = {
    sender_username: string;
    recipient_username: string;
    product_name: string;
    type: string;
    description: string;
    created_at_from: string;
    created_at_to: string;
};

type Props = {
    history?: History[] | PaginatedData<History>;
    types?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    sender_username: '',
    recipient_username: '',
    product_name: '',
    type: '',
    description: '',
    created_at_from: '',
    created_at_to: '',
};

export default function UsageList({ history, types, filters }: Props) {
    setLayoutProps({
        breadcrumbs: [
            { icon: Home, href: dashboard() },
            { title: 'Komisi', href: '#' },
            { title: 'History Kirim Product', href: historySendProduct() },
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
    } = useTableQuery<History, ColumnFilters>({
        endpoint: historySendProduct.url(),
        data: history,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['history', 'filters'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Pengirim',
            sortKey: 'sender_username',
            filter: { type: 'input', key: 'sender_username' },
        },
        {
            label: 'Penerima',
            sortKey: 'recipient_username',
            filter: { type: 'input', key: 'recipient_username' },
        },
        {
            label: 'Detail',
            sortKey: 'product_name',
            filter: { type: 'input', key: 'product_name' },
        },
        {
            label: 'Tipe',
            sortKey: 'type',
            filter: { type: 'select', key: 'type', options: types ?? [] },
        },
        {
            label: 'Keterangan',
            sortKey: 'description',
            filter: { type: 'input', key: 'description' },
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
    ];

    return (
        <>
            <Head title="History Kirim Product" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {'History Kirim Product'}
                            </h1>
                        </div>
                    </div>
                </div>

                <TableContainer
                    data={
                        history && !Array.isArray(history) ? history : undefined
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
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.sender_username}
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {row.recipient_username}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-center whitespace-nowrap">
                                        {row.product_name ?? '-'} (qty :{' '}
                                        {row.qty ?? 0})
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        <EnumBadge
                                            options={types}
                                            value={row.type}
                                        />
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-center whitespace-nowrap">
                                        {row.description ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-medium">
                                    {formatDate(row.created_at)}
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
