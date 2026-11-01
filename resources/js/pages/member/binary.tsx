import { dashboard } from '@/routes';
import { Head, Link, router, setLayoutProps } from '@inertiajs/react';
import {
    ArrowUp,
    Home,
    RotateCcw,
    Search,
    UserCircle,
    UserPlus,
} from 'lucide-react';
import { useState, type SyntheticEvent } from 'react';
import type { Option } from '@/components/table';
import { binaryMember } from '@/actions/App/Modules/Member/Http/Controllers/MemberController';

type BinaryNode = {
    id: number;
    member_id: number;
    username: string;
    position: string;
    rank_id: number | string | null;
    total_omzet?: number | string;
    package?: string;
    level?: number;
};

type NodeStat = {
    left_count: number;
    right_count: number;
    left_pp?: number;
    right_pp?: number;
    left_sm?: number;
    right_sm?: number;
    is_admin: boolean;
};

type DeepestDownline = {
    id: number;
    username: string;
};

type BinaryTreeData = {
    rootNodeId?: number;
    nodes?: Record<string, BinaryNode | null>;
    nodeStats?: Record<string, NodeStat>;
    deepestLeftDownlines?: DeepestDownline[];
    deepestRightDownlines?: DeepestDownline[];
};

type Navigation = {
    showUpButton?: boolean;
    parentUsername?: string | null;
};

type Props = {
    username?: string | null;
    binaryTree?: BinaryTreeData | null;
    navigation?: Navigation | null;
    ranks?: Option[];
};

const formatNumber = (value: number | string | null | undefined): string => {
    const num = Number(value ?? 0);
    return new Intl.NumberFormat('id-ID').format(num);
};

function EnumBadge({
    options,
    value,
}: {
    options?: Option[];
    value?: string | number | null;
}) {
    if (value === undefined || value === null || value === '') return null;
    const item = options?.find(
        (option) => String(option.value) === String(value),
    );
    if (!item) return null;
    return (
        <span
            className={`inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold ${item.color ?? 'bg-muted text-foreground'}`}
        >
            {item.label}
        </span>
    );
}

function NodeCard({
    node,
    stats,
    ranks,
    onSelect,
    size = 'md',
}: {
    node?: BinaryNode | null;
    stats?: NodeStat;
    ranks?: Option[];
    onSelect: (username: string) => void;
    size?: 'lg' | 'md' | 'sm' | 'xs';
}) {
    if (!node) {
        return (
            <Link
                href="#"
                className="group flex h-full w-full min-w-25 cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-border/80 bg-muted/20 p-2 text-center text-muted-foreground/60 shadow-2xs transition-all hover:border-primary/60 hover:bg-primary/5 hover:text-primary"
                title="Daftar member baru"
            >
                <UserPlus className="h-5 w-5 opacity-50 transition-all group-hover:scale-110 group-hover:text-primary group-hover:opacity-100" />
                <span className="mt-1 text-[10px] font-medium group-hover:font-semibold">
                    Kosong
                </span>
            </Link>
        );
    }

    const isAdminNode = stats?.is_admin ?? false;

    // [MODE 1 - ROOT ADMIN NODE]: Tampilan Khusus Node Root Admin
    if (isAdminNode) {
        return (
            <div className="group flex w-full flex-col items-center justify-center rounded-xl border border-border bg-card p-2 text-center shadow-xs">
                <span className="rounded bg-primary/10 px-2 py-0.5 text-[10px] font-bold tracking-wide text-primary">
                    ROOT
                </span>

                <div className="my-1.5 flex flex-col items-center">
                    <UserCircle className="h-6 w-6 text-primary" />
                </div>

                {stats && (
                    <div className="mt-1.5 w-full overflow-hidden rounded-md border border-border/80 bg-background/50 text-[10px]">
                        <div className="grid grid-cols-2 divide-x divide-border/80 font-semibold">
                            <div className="bg-blue-50/40 px-1 py-0.5 text-center text-blue-700 dark:bg-blue-950/20 dark:text-blue-400">
                                L: {stats.left_count}
                            </div>
                            <div className="bg-emerald-50/40 px-1 py-0.5 text-center text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400">
                                R: {stats.right_count}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        );
    }

    // [MODE 2 - MEMBER BINARY NODE]: Kartu Member dengan Statistik Omzet / Pairing / Sponsor
    return (
        <button
            type="button"
            onClick={() => onSelect(node.username)}
            className="group flex w-full cursor-pointer flex-col items-center justify-between rounded-xl border border-border bg-card p-2 text-center shadow-xs transition-all hover:border-primary/50 hover:bg-muted/40 hover:shadow-md"
        >
            <div className="my-1 flex w-full flex-col items-center gap-0.5">
                <UserCircle className="h-6 w-6 text-primary transition-transform group-hover:scale-110" />
                <p className="mt-0.5 max-w-30 truncate text-xs font-bold text-foreground">
                    {node.username}
                </p>
                <div className="mt-0.5">
                    <EnumBadge options={ranks} value={node.rank_id} />
                </div>
            </div>

            {stats && (
                <div className="mt-1.5 w-full overflow-hidden rounded-md border border-border/80 bg-background/50 text-[10px]">
                    {/* Baris 1: Total Downline L & R (Muncul di semua tingkat 1, 2, 3, 4) */}
                    <div
                        className={`grid grid-cols-2 divide-x divide-border/80 font-semibold ${
                            size !== 'xs' ? 'border-b border-border/80' : ''
                        }`}
                    >
                        <div className="bg-blue-50/40 px-1 py-0.5 text-center text-blue-700 dark:bg-blue-950/20 dark:text-blue-400">
                            L: {stats.left_count}
                        </div>
                        <div className="bg-emerald-50/40 px-1 py-0.5 text-center text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400">
                            R: {stats.right_count}
                        </div>
                    </div>

                    {/* Baris 2 & 3: Hanya untuk Tingkat 1, 2, dan 3 (bukan size xs) */}
                    {size !== 'xs' && (
                        <>
                            {/* Baris 2: Pairing Point (PP) */}
                            <div className="grid grid-cols-2 divide-x divide-border/80 border-b border-border/80 text-[9px] font-medium">
                                <div className="truncate bg-blue-50/20 px-1 py-0.5 text-center text-blue-700 dark:bg-blue-950/10 dark:text-blue-400">
                                    PP: {formatNumber(stats.left_pp)}
                                </div>
                                <div className="truncate bg-emerald-50/20 px-1 py-0.5 text-center text-emerald-700 dark:bg-emerald-950/10 dark:text-emerald-400">
                                    PP: {formatNumber(stats.right_pp)}
                                </div>
                            </div>

                            {/* Baris 3: Direct Sponsor L & R */}
                            <div className="grid grid-cols-2 divide-x divide-border/80 text-[9px] font-medium">
                                <div className="bg-blue-50/20 px-1 py-0.5 text-center text-blue-700 dark:bg-blue-950/10 dark:text-blue-400">
                                    L: {stats.left_sm ?? 0}
                                </div>
                                <div className="bg-emerald-50/20 px-1 py-0.5 text-center text-emerald-700 dark:bg-emerald-950/10 dark:text-emerald-400">
                                    R: {stats.right_sm ?? 0}
                                </div>
                            </div>
                        </>
                    )}
                </div>
            )}
        </button>
    );
}

function Level0To1Connector() {
    return (
        <div className="pointer-events-none relative -my-2.5 h-7 w-full">
            <svg
                className="h-full w-full stroke-border/90 dark:stroke-border"
                preserveAspectRatio="none"
            >
                <line x1="50%" y1="0" x2="50%" y2="14" strokeWidth="1.5" />
                <line x1="25%" y1="14" x2="75%" y2="14" strokeWidth="1.5" />
                <line x1="25%" y1="14" x2="25%" y2="28" strokeWidth="1.5" />
                <line x1="75%" y1="14" x2="75%" y2="28" strokeWidth="1.5" />
            </svg>
        </div>
    );
}

function Level1To2Connector() {
    return (
        <div className="pointer-events-none relative -my-2.5 h-7 w-full">
            <svg
                className="h-full w-full stroke-border/90 dark:stroke-border"
                preserveAspectRatio="none"
            >
                {/* Left branch */}
                <line x1="25%" y1="0" x2="25%" y2="14" strokeWidth="1.5" />
                <line x1="12.5%" y1="14" x2="37.5%" y2="14" strokeWidth="1.5" />
                <line x1="12.5%" y1="14" x2="12.5%" y2="28" strokeWidth="1.5" />
                <line x1="37.5%" y1="14" x2="37.5%" y2="28" strokeWidth="1.5" />

                {/* Right branch */}
                <line x1="75%" y1="0" x2="75%" y2="14" strokeWidth="1.5" />
                <line x1="62.5%" y1="14" x2="87.5%" y2="14" strokeWidth="1.5" />
                <line x1="62.5%" y1="14" x2="62.5%" y2="28" strokeWidth="1.5" />
                <line x1="87.5%" y1="14" x2="87.5%" y2="28" strokeWidth="1.5" />
            </svg>
        </div>
    );
}

function Level2To3Connector() {
    return (
        <div className="pointer-events-none relative -my-2.5 h-7 w-full">
            <svg
                className="h-full w-full stroke-border/90 dark:stroke-border"
                preserveAspectRatio="none"
            >
                {/* Branch 1 */}
                <line x1="12.5%" y1="0" x2="12.5%" y2="14" strokeWidth="1.5" />
                <line
                    x1="6.25%"
                    y1="14"
                    x2="18.75%"
                    y2="14"
                    strokeWidth="1.5"
                />
                <line x1="6.25%" y1="14" x2="6.25%" y2="28" strokeWidth="1.5" />
                <line
                    x1="18.75%"
                    y1="14"
                    x2="18.75%"
                    y2="28"
                    strokeWidth="1.5"
                />

                {/* Branch 2 */}
                <line x1="37.5%" y1="0" x2="37.5%" y2="14" strokeWidth="1.5" />
                <line
                    x1="31.25%"
                    y1="14"
                    x2="43.75%"
                    y2="14"
                    strokeWidth="1.5"
                />
                <line
                    x1="31.25%"
                    y1="14"
                    x2="31.25%"
                    y2="28"
                    strokeWidth="1.5"
                />
                <line
                    x1="43.75%"
                    y1="14"
                    x2="43.75%"
                    y2="28"
                    strokeWidth="1.5"
                />

                {/* Branch 3 */}
                <line x1="62.5%" y1="0" x2="62.5%" y2="14" strokeWidth="1.5" />
                <line
                    x1="56.25%"
                    y1="14"
                    x2="68.75%"
                    y2="14"
                    strokeWidth="1.5"
                />
                <line
                    x1="56.25%"
                    y1="14"
                    x2="56.25%"
                    y2="28"
                    strokeWidth="1.5"
                />
                <line
                    x1="68.75%"
                    y1="14"
                    x2="68.75%"
                    y2="28"
                    strokeWidth="1.5"
                />

                {/* Branch 4 */}
                <line x1="87.5%" y1="0" x2="87.5%" y2="14" strokeWidth="1.5" />
                <line
                    x1="81.25%"
                    y1="14"
                    x2="93.75%"
                    y2="14"
                    strokeWidth="1.5"
                />
                <line
                    x1="81.25%"
                    y1="14"
                    x2="81.25%"
                    y2="28"
                    strokeWidth="1.5"
                />
                <line
                    x1="93.75%"
                    y1="14"
                    x2="93.75%"
                    y2="28"
                    strokeWidth="1.5"
                />
            </svg>
        </div>
    );
}

export default function BinaryTreePage({
    username,
    binaryTree,
    navigation,
    ranks,
}: Props) {
    const [searchInput, setSearchInput] = useState('');

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
                title: 'Jaringan Binary',
                href: binaryMember(),
            },
        ],
    });

    const handleSelectNode = (targetUsername: string) => {
        router.visit(binaryMember.url(targetUsername));
    };

    const handleResetToSelf = () => {
        router.visit(binaryMember.url());
    };

    const handleNavigateUp = () => {
        if (navigation?.parentUsername) {
            router.visit(binaryMember.url(navigation.parentUsername));
        }
    };

    const handleSearch = (e: SyntheticEvent) => {
        e.preventDefault();
        const cleanSearch = searchInput.trim();
        if (cleanSearch) {
            router.visit(binaryMember.url(cleanSearch));
        }
    };

    const nodes = binaryTree?.nodes ?? {};
    const stats = binaryTree?.nodeStats ?? {};
    const isViewingSelf = !username;

    return (
        <>
            <Head title="Jaringan Binary" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                {/* Header Row */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    {/* Left: Title */}
                    <div className="flex items-center gap-2">
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">
                            Pohon Jaringan Saya
                        </h1>
                    </div>

                    {/* [NAVIGASI UPLINE & ROOT RESET]: Tombol Navigasi Pohon Jaringan */}
                    <div className="flex flex-1 items-center justify-center gap-2">
                        {navigation?.showUpButton &&
                            navigation?.parentUsername && (
                                <button
                                    type="button"
                                    onClick={handleNavigateUp}
                                    className="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-border bg-muted/40 px-3 text-xs font-semibold text-foreground transition-all hover:bg-muted"
                                >
                                    <ArrowUp className="size-3.5" />
                                    Upline
                                </button>
                            )}

                        {!isViewingSelf && (
                            <button
                                type="button"
                                onClick={handleResetToSelf}
                                className="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-border bg-muted/40 px-3 text-xs font-semibold text-foreground transition-all hover:bg-muted"
                            >
                                <RotateCcw className="size-3.5" />
                                Root
                            </button>
                        )}
                    </div>

                    {/* Right: Search Form */}
                    <form
                        onSubmit={handleSearch}
                        className="flex items-center gap-2"
                    >
                        <div className="relative">
                            <Search className="absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                            <input
                                type="text"
                                value={searchInput}
                                onChange={(e) => setSearchInput(e.target.value)}
                                placeholder="Cari username member..."
                                className="h-9 w-64 rounded-lg border border-border bg-background pr-3 pl-8 text-sm text-foreground shadow-xs outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            />
                        </div>
                        <button
                            type="submit"
                            className="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-lg bg-primary px-3.5 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90"
                        >
                            Cari
                        </button>
                    </form>
                </div>

                {/* [SHORTCUT DOWNLINE TERJAUH]: Navigasi Cepat Menuju Kaki Kiri / Kanan Terbawah */}
                <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                    {/* Kiri Terjauh */}
                    <div className="flex flex-col gap-1.5 rounded-xl border border-border bg-card p-3 shadow-xs">
                        <div className="flex items-center justify-center text-xs font-semibold text-blue-700 dark:text-blue-400">
                            <span className="flex items-center gap-1">
                                Level Kiri Terbawah
                            </span>
                        </div>
                        <div className="flex flex-wrap items-center justify-center gap-1.5 pt-1">
                            {binaryTree?.deepestLeftDownlines &&
                            binaryTree.deepestLeftDownlines.length > 0 ? (
                                binaryTree.deepestLeftDownlines.map((dl) => (
                                    <button
                                        key={`deepest-left-${dl.id}`}
                                        type="button"
                                        onClick={() =>
                                            handleSelectNode(dl.username)
                                        }
                                        className="inline-flex cursor-pointer items-center rounded-md border border-blue-200 bg-blue-50/50 px-2.5 py-1 text-xs font-semibold text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                    >
                                        {dl.username}
                                    </button>
                                ))
                            ) : (
                                <span className="text-center text-xs text-muted-foreground">
                                    Belum ada downline kaki kiri.
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Kanan Terjauh */}
                    <div className="flex flex-col gap-1.5 rounded-xl border border-border bg-card p-3 shadow-xs">
                        <div className="flex items-center justify-center text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            <span className="flex items-center gap-1">
                                Level Kanan Terbawah
                            </span>
                        </div>
                        <div className="flex flex-wrap items-center justify-center gap-1.5 pt-1">
                            {binaryTree?.deepestRightDownlines &&
                            binaryTree.deepestRightDownlines.length > 0 ? (
                                binaryTree.deepestRightDownlines.map((dl) => (
                                    <button
                                        key={`deepest-right-${dl.id}`}
                                        type="button"
                                        onClick={() =>
                                            handleSelectNode(dl.username)
                                        }
                                        className="inline-flex cursor-pointer items-center rounded-md border border-emerald-200 bg-emerald-50/50 px-2.5 py-1 text-xs font-semibold text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50"
                                    >
                                        {dl.username}
                                    </button>
                                ))
                            ) : (
                                <span className="text-center text-xs text-muted-foreground">
                                    Belum ada downline kaki kanan.
                                </span>
                            )}
                        </div>
                    </div>
                </div>

                {/* Binary Tree Visual Container (Level 0 - 3) */}
                <div className="overflow-x-auto rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div className="mx-auto flex min-w-190 flex-col items-center gap-6">
                        {/* Level 0: Root */}
                        <div className="flex w-full justify-center">
                            <div className="w-56">
                                <NodeCard
                                    node={nodes['root']}
                                    stats={stats['root']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="lg"
                                />
                            </div>
                        </div>

                        {/* Connector Level 0 -> Level 1 */}
                        <Level0To1Connector />

                        {/* Level 1: 2 Nodes */}
                        <div className="grid w-full grid-cols-2 gap-8">
                            <div className="mx-auto w-48">
                                <NodeCard
                                    node={nodes['left']}
                                    stats={stats['left']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="md"
                                />
                            </div>
                            <div className="mx-auto w-48">
                                <NodeCard
                                    node={nodes['right']}
                                    stats={stats['right']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="md"
                                />
                            </div>
                        </div>

                        {/* Connector Level 1 -> Level 2 */}
                        <Level1To2Connector />

                        {/* Level 2: 4 Nodes */}
                        <div className="grid w-full grid-cols-4 gap-4">
                            <div className="mx-auto w-40">
                                <NodeCard
                                    node={nodes['leftLeft']}
                                    stats={stats['leftLeft']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="sm"
                                />
                            </div>
                            <div className="mx-auto w-40">
                                <NodeCard
                                    node={nodes['leftRight']}
                                    stats={stats['leftRight']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="sm"
                                />
                            </div>
                            <div className="mx-auto w-40">
                                <NodeCard
                                    node={nodes['rightLeft']}
                                    stats={stats['rightLeft']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="sm"
                                />
                            </div>
                            <div className="mx-auto w-40">
                                <NodeCard
                                    node={nodes['rightRight']}
                                    stats={stats['rightRight']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="sm"
                                />
                            </div>
                        </div>

                        {/* Connector Level 2 -> Level 3 */}
                        <Level2To3Connector />

                        {/* Level 3: 8 Nodes */}
                        <div className="grid w-full grid-cols-8 gap-2">
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['leftLeftLeft']}
                                    stats={stats['leftLeftLeft']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['leftLeftRight']}
                                    stats={stats['leftLeftRight']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['leftRightLeft']}
                                    stats={stats['leftRightLeft']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['leftRightRight']}
                                    stats={stats['leftRightRight']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['rightLeftLeft']}
                                    stats={stats['rightLeftLeft']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['rightLeftRight']}
                                    stats={stats['rightLeftRight']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['rightRightLeft']}
                                    stats={stats['rightRightLeft']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                            <div className="mx-auto w-24">
                                <NodeCard
                                    node={nodes['rightRightRight']}
                                    stats={stats['rightRightRight']}
                                    ranks={ranks}
                                    onSelect={handleSelectNode}
                                    size="xs"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
