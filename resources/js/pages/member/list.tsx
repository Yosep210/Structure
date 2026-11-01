import { start } from '@/actions/App/Modules/Auth/Http/Controllers/ImpersonateController';
import {
    binaryMember,
    memberList,
    memberStatus,
} from '@/actions/App/Modules/Member/Http/Controllers/MemberController';
import {
    DataTable,
    EnumBadge,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import TableContainer from '@/components/table/paginates/table-container';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { PaginatedData } from '@/components/table/paginates/pagination';
import { Head, router, setLayoutProps } from '@inertiajs/react';
import {
    Plus,
    Users,
    Mail,
    Phone,
    Network,
    Home,
    ShieldCheck,
    ShieldBan,
    HatGlasses,
} from 'lucide-react';

type Member = {
    id: number;
    username?: string;
    name?: string;
    email?: string;
    phone?: string | null;
    rank_id?: number | string | null;
    position?: string | null;
    sponsor_username?: string | null;
    sponsor_name?: string | null;
    parent_username?: string | null;
    parent_name?: string | null;
    status?: number | string;
    created_at?: string;
    last_login?: string;
};

type ColumnFilters = {
    username: string;
    name: string;
    rank_id: string;
    contact: string;
    sponsor: string;
    parent: string;
    position: string;
    status: string;
    created_at_from: string;
    created_at_to: string;
    last_login_from: string;
    last_login_to: string;
};

type Props = {
    member?: Member[] | PaginatedData<Member>;
    ranks?: Option[];
    statuses?: Option[];
    positions?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    username: '',
    name: '',
    rank_id: '',
    contact: '',
    sponsor: '',
    parent: '',
    position: '',
    status: '',
    created_at_from: '',
    created_at_to: '',
    last_login_from: '',
    last_login_to: '',
};

export default function MemberList({
    member,
    ranks,
    positions,
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
                title: 'Member',
                href: '#',
            },
            {
                title: 'List Member',
                href: memberList(),
            },
        ],
    });

    const handleStatus = (username: string, currentStatus: string | number) => {
        router.patch(memberStatus.url(username), {
            status: Number(currentStatus) === 1 ? 0 : 1,
        });
    };

    const handleBinary = (username: string) => {
        router.visit(binaryMember.url(username));
    };

    const handleLogin = (username: string) => {
        router.visit(start.url(username));
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
    } = useTableQuery<Member, ColumnFilters>({
        endpoint: memberList.url(),
        data: member,
        filters,
        defaultSortKey: 'created_at',
        defaultSortDirection: 'desc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['member'],
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
            label: 'Rank',
            sortKey: 'rank_id',
            filter: { type: 'select', key: 'rank_id', options: ranks ?? [] },
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
            label: 'Upline',
            sortKey: 'parent',
            filter: { type: 'input', key: 'parent' },
        },
        {
            label: 'Posisi',
            sortKey: 'position',
            filter: {
                type: 'select',
                key: 'position',
                options: positions ?? [],
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
            label: 'Terakhir Login',
            sortKey: 'last_login',
            filter: {
                type: 'date',
                fromKey: 'last_login_from',
                toKey: 'last_login_to',
            },
        },
    ];

    return (
        <>
            <Head title="List Member" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                List Member
                            </h1>
                        </div>
                    </div>

                    <button
                        type="button"
                        className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                    >
                        <Plus className="h-4 w-4" />
                        Tambah Member Baru
                    </button>
                </div>

                <TableContainer
                    data={member && !Array.isArray(member) ? member : undefined}
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
                                    {row.username ?? '-'}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        {row.name ?? '-'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="inline-flex justify-start whitespace-nowrap">
                                        <EnumBadge
                                            options={ranks}
                                            value={row.rank_id}
                                        />
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <div className="flex flex-col justify-start gap-1">
                                        <span className="inline-flex items-center gap-1.5 font-medium text-foreground">
                                            <Mail className="h-3.5 w-3.5 shrink-0" />
                                            <span>{row.email ?? '-'}</span>
                                        </span>
                                        {row.phone ? (
                                            <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                                                <Phone className="h-3.5 w-3.5 shrink-0" />
                                                <span>{row.phone}</span>
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
                                    <div className="flex flex-col items-center justify-center">
                                        <span className="font-medium text-foreground">
                                            <div className="inline-flex justify-start whitespace-nowrap">
                                                {row.parent_username ?? '-'}
                                            </div>
                                        </span>
                                        {row.parent_name ? (
                                            <span className="text-xs text-muted-foreground">
                                                <div className="inline-flex justify-start whitespace-nowrap">
                                                    {row.parent_name}
                                                </div>
                                            </span>
                                        ) : null}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <EnumBadge
                                        options={positions}
                                        value={row.position}
                                    />
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <EnumBadge
                                        options={statuses}
                                        value={row.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-center">
                                    {formatDate(row.created_at)}
                                </td>
                                <td className="px-4 py-3 text-center">
                                    {formatDate(row.last_login)}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center justify-center gap-1.5">
                                        {/* [AKSI 1 - UBAH STATUS]: Toggle Aktif / Non-Aktif Member */}
                                        <button
                                            type="button"
                                            title={
                                                String(row.status) === '1'
                                                    ? 'Status: Active'
                                                    : 'Status: Inactive'
                                            }
                                            aria-label={
                                                String(row.status) === '1'
                                                    ? 'Ubah status member menjadi inactive'
                                                    : 'Ubah status member menjadi active'
                                            }
                                            className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                            onClick={() =>
                                                handleStatus(
                                                    row.username as string,
                                                    row.status as
                                                        | string
                                                        | number,
                                                )
                                            }
                                        >
                                            {String(row.status) === '1' ? (
                                                <ShieldCheck className="h-3.5 w-3.5 text-green-500" />
                                            ) : (
                                                <ShieldBan className="h-3.5 w-3.5 text-red-500" />
                                            )}
                                        </button>
                                        {/* [AKSI 2 - POHON BINARY]: Buka Halaman Binary Tree Member */}
                                        <button
                                            type="button"
                                            title="Jaringan Pohon"
                                            aria-label="Lihat Jaringan Binary Tree"
                                            className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                            onClick={() =>
                                                handleBinary(
                                                    row.username as string,
                                                )
                                            }
                                        >
                                            <Network className="h-3.5 w-3.5 text-blue-500" />
                                        </button>
                                        {/* [AKSI 3 - IMPERSONASI]: Login Sebagai Member (Admin Saja) */}
                                        <button
                                            type="button"
                                            title="Login"
                                            aria-label="Login sebagai member"
                                            className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                            onClick={() =>
                                                handleLogin(
                                                    row.username as string,
                                                )
                                            }
                                        >
                                            <HatGlasses className="h-3.5 w-3.5 text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </>
                        )}
                        emptyIcon={Users}
                    />
                </TableContainer>
            </div>
        </>
    );
}
