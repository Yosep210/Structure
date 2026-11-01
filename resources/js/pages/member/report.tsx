import { reportReg } from '@/actions/App/Modules/Member/Http/Controllers/MemberController';
import {
    DataTable,
    EnumBadge,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import { useTableQuery } from '@/hooks/table/use-table-query';
import TableContainer from '@/components/table/paginates/table-container';
import { formatCurrency, formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, setLayoutProps } from '@inertiajs/react';
import { Home, Mail, Phone, Users } from 'lucide-react';

type Registration = {
    id: number;
    pendaftar_username: string | null;
    pendaftar_name: string | null;
    member_username: string;
    member_name: string;
    member_email: string | null;
    member_phone: string | null;
    sponsor_username: string | null;
    sponsor_name: string | null;
    access: string | null;
    bv: number | string;
    total_payment: number | string;
    member_status: number | string;
    created_at: string;
    dateconfirmed: string | null;
};

type ColumnFilters = {
    pendaftar: string;
    member: string;
    contact: string;
    sponsor: string;
    access: string;
    bv_min: string;
    bv_max: string;
    total_payment_min: string;
    total_payment_max: string;
    status: string;
    created_at_from: string;
    created_at_to: string;
    dateconfirmed_from: string;
    dateconfirmed_to: string;
};

type Props = {
    report?: Registration[] | PaginatedData<Registration>;
    accesses?: Option[];
    statuses?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    pendaftar: '',
    member: '',
    contact: '',
    sponsor: '',
    access: '',
    bv_min: '',
    bv_max: '',
    total_payment_min: '',
    total_payment_max: '',
    status: '',
    created_at_from: '',
    created_at_to: '',
    dateconfirmed_from: '',
    dateconfirmed_to: '',
};

export default function RegistrationReport({
    report,
    accesses,
    statuses,
    filters,
}: Props) {
    setLayoutProps({
        breadcrumbs: [
            {
                icon: Home,
                href: dashboard(),
            },
            {
                title: 'Laporan',
                href: '#',
            },
            {
                title: 'Pendaftaran Member',
                href: reportReg(),
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
    } = useTableQuery<Registration, ColumnFilters>({
        endpoint: reportReg.url(),
        data: report,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['report', 'filters'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Pendaftar',
            sortKey: 'pendaftar',
            filter: { type: 'input', key: 'pendaftar' },
        },
        {
            label: 'Member',
            sortKey: 'member',
            filter: { type: 'input', key: 'member' },
        },
        {
            label: 'Kontak',
            sortKey: 'contact',
            filter: { type: 'input', key: 'contact' },
        },
        {
            label: 'Sponsor',
            sortKey: 'sponsor',
            filter: { type: 'input', key: 'sponsor' },
        },
        {
            label: 'Akses Pendaftaran',
            sortKey: 'access',
            filter: { type: 'input', key: 'access' },
        },
        {
            label: 'BV',
            sortKey: 'bv',
            filter: {
                type: 'range',
                minKey: 'bv_min',
                maxKey: 'bv_max',
            },
        },
        {
            label: 'Total Pembayaran',
            sortKey: 'total_payment',
            filter: {
                type: 'range',
                minKey: 'total_payment_min',
                maxKey: 'total_payment_max',
            },
        },
        {
            label: 'Status',
            sortKey: 'status',
            filter: {
                type: 'select',
                key: 'status',
                options: statuses ?? [],
            },
        },
        {
            label: 'Tanggal Daftar',
            sortKey: 'created_at',
            filter: {
                type: 'date',
                fromKey: 'created_at_from',
                toKey: 'created_at_to',
            },
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
            <Head title="Pendaftaran" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                Pendaftaran Member
                            </h1>
                        </div>
                    </div>
                </div>

                <TableContainer
                    data={report && !Array.isArray(report) ? report : undefined}
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
                                <td className="px-4 py-3 text-center">
                                    <div className="flex flex-col items-center justify-center">
                                        <span className="font-medium text-foreground">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.pendaftar_username ?? '-'}
                                            </div>
                                        </span>
                                        {row.pendaftar_name ? (
                                            <span className="text-xs text-muted-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.pendaftar_name}
                                                </div>
                                            </span>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex flex-col justify-start gap-1">
                                        <span className="inline-flex items-center gap-1.5 font-medium text-foreground uppercase">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.member_username ?? '-'}
                                            </div>
                                        </span>
                                        {row.member_name ? (
                                            <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.member_name}
                                                </div>
                                            </span>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex flex-col justify-start gap-1">
                                        <span className="inline-flex items-center gap-1.5 font-medium text-foreground">
                                            <Mail className="h-3.5 w-3.5 shrink-0" />
                                            <span>
                                                {row.member_email ?? '-'}
                                            </span>
                                        </span>
                                        {row.member_phone ? (
                                            <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                                                <Phone className="h-3.5 w-3.5 shrink-0" />
                                                <span>{row.member_phone}</span>
                                            </span>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex flex-col items-center justify-center">
                                        <span className="font-medium text-foreground">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.sponsor_username ?? '-'}
                                            </div>
                                        </span>
                                        {row.sponsor_name ? (
                                            <span className="text-xs text-muted-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.sponsor_name}
                                                </div>
                                            </span>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        <EnumBadge
                                            options={accesses}
                                            value={row.access}
                                        />
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center font-mono">
                                    {new Intl.NumberFormat('id-ID').format(
                                        Number(row.bv),
                                    )}{' '}
                                    {'BV'}
                                </td>
                                <td className="px-4 py-3 text-center font-mono">
                                    {formatCurrency(row.total_payment)}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <EnumBadge
                                        options={statuses}
                                        value={row.member_status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-center">
                                    {formatDate(row.created_at)}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    {formatDate(row.dateconfirmed)}
                                </td>
                                <td className="px-4 py-3 text-center"></td>
                            </>
                        )}
                        emptyIcon={Users}
                    />
                </TableContainer>
            </div>
        </>
    );
}
