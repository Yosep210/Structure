import {
    EnumBadge,
    DataTable,
    SortableFilterableColumn,
    type Option,
} from '@/components/table';
import TableContainer from '@/components/table/paginates/table-container';
import { useTableQuery } from '@/hooks/table/use-table-query';
import { formatCurrency } from '@/lib/format';
import type { PaginatedData } from '@/components/table/paginates/pagination';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Head, router, setLayoutProps, usePage } from '@inertiajs/react';
import { ChevronDown, Home, Network, User, UserCircle } from 'lucide-react';
import { useState } from 'react';
import { dashboard } from '@/routes';
import { generationList } from '@/actions/App/Modules/Member/Http/Controllers/MemberController';

type Generation = {
    id: number;
    member_username: string;
    member_name?: string;
    network_id?: number;
    gen?: number;
    gen_level?: number;
    group?: number;
    group_level?: number;
    level?: number;
    total_omzet?: number | string;
    sponsor_username?: string | null;
    sponsor_name?: string | null;
};

type ColumnFilters = {
    member_username: string;
    member_name: string;
    sponsor_username: string;
    gen_min: string;
    gen_max: string;
    group_min: string;
    group_max: string;
    total_omzet_min: string;
    total_omzet_max: string;
};

type DownlineNode = {
    id: number;
    name: string;
    username: string;
    gen: number;
    rank_id: number | string | null;
    rank: string;
    children?: DownlineNode[];
};

type GenerationTree = {
    targetMember?: {
        name?: string | null;
        username?: string | null;
    } | null;
    targetNode?: {
        total_omzet?: number | string | null;
        rank_id?: number | string | null;
    } | null;
    stats?: {
        total_generations?: number;
        total_groups?: number;
    } | null;
    rankName?: string | null;
    treeData?: DownlineNode[];
    showInfoCard?: boolean;
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
    generation?: Generation[] | PaginatedData<Generation>;
    username?: string | number | null;
    generationTree?: GenerationTree | null;
    ranks?: Option[];
    statuses?: Option[];
    filters?: Partial<ColumnFilters> & {
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number | 'all';
    };
};

const defaultColumnFilters: ColumnFilters = {
    member_username: '',
    member_name: '',
    sponsor_username: '',
    gen_min: '',
    gen_max: '',
    group_min: '',
    group_max: '',
    total_omzet_min: '',
    total_omzet_max: '',
};

const generationBadgeClass = (value: unknown): string => {
    const classes: Record<string, string> = {
        '1': 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30',
        '2': 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/30',
        '3': 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/30',
    };

    return classes[String(value)] ?? 'bg-muted text-foreground ring-border';
};

const groupBadgeClass = (value: unknown): string => {
    const classes: Record<string, string> = {
        '1': 'bg-cyan-50 text-cyan-700 ring-cyan-600/20 dark:bg-cyan-500/10 dark:text-cyan-400 dark:ring-cyan-500/30',
        '2': 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30',
        '3': 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/30',
    };

    return classes[String(value)] ?? 'bg-muted text-foreground ring-border';
};

function DownlineList({
    nodes,
    level,
    ranks,
    onSelect,
}: {
    nodes: DownlineNode[];
    level: number;
    ranks?: Option[];
    onSelect: (username: string) => void;
}) {
    return (
        <div className="space-y-1">
            {nodes.map((node) =>
                node.children && node.children.length > 0 ? (
                    <Collapsible
                        key={node.id}
                        defaultOpen={false}
                        className="space-y-1"
                        style={{ marginLeft: `${(level - 1) * 12}px` }}
                    >
                        <div className="flex items-center gap-1">
                            <CollapsibleTrigger className="inline-flex size-7 shrink-0 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground [&[data-state=open]>svg]:rotate-180">
                                <ChevronDown className="size-3.5 transition-transform" />
                            </CollapsibleTrigger>
                            <button
                                type="button"
                                className="flex min-w-0 flex-1 items-center gap-3 rounded-md border border-border/70 bg-muted/20 px-2 py-1.5 text-left hover:bg-muted"
                                onClick={() => onSelect(node.username)}
                            >
                                <User className="h-5 w-5 text-primary" />
                                <span className="min-w-0 truncate text-xs text-foreground">
                                    {node.name} ({node.username})
                                </span>
                                <span className="inline-flex shrink-0 items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 ring-1 ring-blue-600/20 ring-inset dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30">
                                    Gen {node.gen}
                                </span>
                                <EnumBadge
                                    options={ranks}
                                    value={node.rank_id}
                                />
                            </button>
                        </div>
                        <CollapsibleContent className="space-y-1">
                            <DownlineList
                                nodes={node.children}
                                level={level + 1}
                                ranks={ranks}
                                onSelect={onSelect}
                            />
                        </CollapsibleContent>
                    </Collapsible>
                ) : (
                    <button
                        key={node.id}
                        type="button"
                        className="flex w-full items-center gap-3 rounded-md border border-border/70 bg-muted/20 px-2 py-1.5 text-left hover:bg-muted"
                        style={{ marginLeft: `${(level - 1) * 12}px` }}
                        onClick={() => onSelect(node.username)}
                    >
                        <User className="h-5 w-5 text-primary" />
                        <span className="min-w-0 truncate text-xs text-foreground">
                            {node.name} ({node.username})
                        </span>
                        <span className="inline-flex shrink-0 items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 ring-1 ring-blue-600/20 ring-inset dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30">
                            Gen {node.gen}
                        </span>
                        <EnumBadge options={ranks} value={node.rank_id} />
                    </button>
                ),
            )}
        </div>
    );
}

export default function GenerationList({
    generation,
    username,
    generationTree,
    ranks,
    filters,
}: Props) {
    const { auth } = usePage<PageProps>().props;
    const isAdmin = auth?.user?.roles?.includes('admin') ?? false;
    const showGroup = !isAdmin || !!username;
    const [isInfoCardOpen, setIsInfoCardOpen] = useState(true);
    const [isSelectedMemberOpen, setIsSelectedMemberOpen] = useState(false);

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
                title: 'Jaringan Generasi',
                href: generationList(),
            },
        ],
    });

    const handleGen = (username: string) => {
        router.visit(generationList.url(username));
    };

    const groupColumn: SortableFilterableColumn<ColumnFilters> = {
        label: 'Grup',
        sortKey: 'group',
        filter: {
            type: 'range',
            minKey: 'group_min',
            maxKey: 'group_max',
        },
    };

    const columns: SortableFilterableColumn<ColumnFilters>[] = [
        {
            label: 'Member',
            sortKey: 'member_username',
            filter: { type: 'input', key: 'member_username' },
        },
        {
            label: 'Sponsor',
            sortKey: 'sponsor_username',
            filter: { type: 'input', key: 'sponsor_username' },
        },
        {
            label: 'Generasi',
            sortKey: 'gen',
            filter: {
                type: 'range',
                minKey: 'gen_min',
                maxKey: 'gen_max',
            },
        },
        ...(showGroup ? [groupColumn] : []),
        {
            label: 'Total Omzet',
            sortKey: 'total_omzet',
            filter: {
                type: 'range',
                minKey: 'total_omzet_min',
                maxKey: 'total_omzet_max',
            },
        },
    ];

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
    } = useTableQuery<Generation, ColumnFilters>({
        endpoint: generationList.url(username ?? undefined),
        data: generation,
        filters,
        defaultSortKey: 'gen',
        defaultSortDirection: 'asc',
        defaultPerPage: 10,
        defaultFilters: defaultColumnFilters,
        only: ['generation'],
    });

    const rowStart =
        perPage === 'all' ? 1 : (currentPage - 1) * Number(perPage) + 1;
    const selectedMemberName =
        generationTree?.targetMember?.name ?? auth?.user?.name ?? '-';
    const selectedMemberUsername =
        generationTree?.targetMember?.username ??
        username ??
        auth?.user?.username ??
        '-';
    const generationStats = generationTree?.stats;

    return (
        <>
            <Head title="Jaringan Generasi" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-2">
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">
                            List Generasi Member
                        </h1>
                    </div>
                </div>

                {/* [MODE MEMBER & ADMIN AUDIT]: Ringkasan Jaringan & Pohon Generasi */}
                {generationTree?.showInfoCard && (
                    <Collapsible
                        open={isInfoCardOpen}
                        onOpenChange={setIsInfoCardOpen}
                        className="rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div className="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-b border-border px-3 py-2">
                            <p className="justify-self-start text-xs font-semibold text-foreground">
                                Jaringan Generasi
                            </p>
                            <div className="flex flex-wrap items-center justify-center gap-1.5 justify-self-center">
                                {[
                                    [
                                        'Total Generasi',
                                        generationStats?.total_generations ?? 0,
                                    ],
                                    [
                                        'Total Grup',
                                        generationStats?.total_groups ?? 0,
                                    ],
                                    [
                                        'Total Omzet',
                                        formatCurrency(
                                            generationTree.targetNode
                                                ?.total_omzet,
                                        ),
                                    ],
                                ].map(([label, value]) => (
                                    <div
                                        key={label}
                                        className="rounded-md border border-border px-2 py-1"
                                    >
                                        <p className="text-center text-[11px]">
                                            {label} : {value}
                                        </p>
                                    </div>
                                ))}
                            </div>
                            <CollapsibleTrigger className="inline-flex size-9 items-center justify-center justify-self-end rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground [&[data-state=open]>svg]:rotate-180">
                                <ChevronDown className="size-4 transition-transform" />
                                <span className="sr-only">
                                    {isInfoCardOpen
                                        ? 'Tutup ringkasan jaringan'
                                        : 'Buka ringkasan jaringan'}
                                </span>
                            </CollapsibleTrigger>
                        </div>
                        <CollapsibleContent>
                            <Collapsible
                                open={isSelectedMemberOpen}
                                onOpenChange={setIsSelectedMemberOpen}
                                className="px-3 py-2"
                            >
                                <CollapsibleTrigger className="group flex w-full items-center gap-2 rounded-md px-1 py-1 text-left hover:bg-muted">
                                    <ChevronDown className="size-4 shrink-0 text-muted-foreground transition-transform group-data-[state=open]:rotate-180" />
                                    <UserCircle className="h-5 w-5 text-primary" />
                                    <p className="text-sm font-bold tracking-tight text-foreground">
                                        {selectedMemberName} (
                                        {selectedMemberUsername})
                                    </p>
                                    <p className="inline-flex shrink-0 items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-600/20 ring-inset dark:text-amber-400 dark:ring-amber-500/30">
                                        Anda
                                    </p>
                                    <EnumBadge
                                        options={ranks}
                                        value={
                                            generationTree.targetNode?.rank_id
                                        }
                                    />
                                </CollapsibleTrigger>
                                <CollapsibleContent className="space-y-2 pt-2">
                                    {generationTree.treeData &&
                                    generationTree.treeData.length > 0 ? (
                                        <DownlineList
                                            nodes={generationTree.treeData}
                                            level={1}
                                            ranks={ranks}
                                            onSelect={handleGen}
                                        />
                                    ) : (
                                        <p className="text-xs text-muted-foreground">
                                            Belum ada downline.
                                        </p>
                                    )}
                                </CollapsibleContent>
                            </Collapsible>
                        </CollapsibleContent>
                    </Collapsible>
                )}

                <TableContainer
                    data={
                        generation && !Array.isArray(generation)
                            ? generation
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
                                <td className="px-4 py-3">
                                    <div className="flex flex-col justify-start">
                                        <span className="font-medium text-foreground uppercase">
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
                                    <div className="flex flex-col justify-start">
                                        <span className="font-medium text-foreground uppercase">
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
                                <td className="px-4 py-3 text-center font-semibold">
                                    <span
                                        className={`inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset ${generationBadgeClass(row.gen_level ?? row.gen)}`}
                                    >
                                        Gen {row.gen_level ?? row.gen ?? 0}
                                    </span>
                                </td>
                                {/* [MODE MEMBER & ADMIN AUDIT]: Data Cell Kolom Grup */}
                                {showGroup && (
                                    <td className="px-4 py-3 text-center">
                                        <span
                                            className={`inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset ${groupBadgeClass(row.group_level)}`}
                                        >
                                            Grup {row.group_level ?? '-'}
                                        </span>
                                    </td>
                                )}
                                <td className="px-4 py-3 text-center font-mono">
                                    {formatCurrency(row.total_omzet)}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center justify-center gap-1.5">
                                        <button
                                            type="button"
                                            title="Detail"
                                            aria-label="Lihat Detail Generation Member"
                                            className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                                            onClick={() =>
                                                handleGen(
                                                    row.member_username as string,
                                                )
                                            }
                                        >
                                            <Network className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </>
                        )}
                        emptyIcon={Network}
                    />
                </TableContainer>
            </div>
        </>
    );
}
