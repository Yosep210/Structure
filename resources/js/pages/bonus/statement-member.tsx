import { statementMember } from '@/actions/App/Modules/Bonus/Http/Controllers/BonusController';
import { FilterDate } from '@/components/table';
import { formatCurrency } from '@/lib/format';
import { dashboard } from '@/routes';
import { statements } from '@/routes/bonus';
import { Head, router, setLayoutProps } from '@inertiajs/react';
import { ArrowLeftToLine, Home } from 'lucide-react';

type BonusItem = {
    type: number;
    label: string;
    color: string;
    amount: number;
};

type Summary = {
    total_bonus?: number;
    total_tax?: number;
    total_autosave?: number;
    total_ewc?: number;
    gross?: number;
    tax?: number;
    net?: number;
};

type TargetMember = {
    id?: number;
    name?: string | null;
    username?: string | null;
    email?: string | null;
};

type Filters = {
    date_from: string;
    date_to: string;
};

type Props = {
    targetMember?: TargetMember | null;
    username?: string | null;
    items?: BonusItem[];
    summary?: Summary;
    filters?: Filters;
};

export default function BonusStatementMember({
    targetMember,
    username,
    items = [],
    summary = {
        total_bonus: 0,
        total_tax: 0,
        total_autosave: 0,
        total_ewc: 0,
        gross: 0,
        tax: 0,
        net: 0,
    },
    filters = { date_from: '', date_to: '' },
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
                title: 'Statement Komisi',
                href: statementMember(),
            },
        ],
    });

    const memberName = targetMember?.name ?? '-';
    const memberUsername = targetMember?.username ?? '-';

    const handleDateChange = (from: string, to: string) => {
        router.get(statementMember.url(username ?? undefined), {
            date_from: from,
            date_to: to,
        });
    };

    const handleBack = () => {
        router.get(statements.url(), {
            date_from: filters.date_from,
            date_to: filters.date_to,
        });
    };

    const totalBonusVal = summary.total_bonus ?? summary.gross ?? 0;
    const totalTaxVal = summary.total_tax ?? summary.tax ?? 0;
    const totalAutoSaveVal = summary.total_autosave ?? 0;
    const totalEwcVal = summary.total_ewc ?? summary.net ?? 0;

    return (
        <>
            <Head title="Statement Detail" />

            <div className="flex h-full flex-1 flex-col gap-5 p-6">
                {/* 1. Judul Halaman between dengan Target */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-2">
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">
                            Statement Detail
                        </h1>
                    </div>

                    {/* [KHUSUS ADMIN VIEW AS / DETAIL MEMBER]: Info Member Target */}
                    {targetMember && (
                        <div className="flex items-center gap-2">
                            <span className="text-sm font-bold tracking-tight text-foreground">
                                Member : {memberName} ({memberUsername})
                            </span>
                        </div>
                    )}
                </div>

                {/* 2. FilterDate between dengan Kembali */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="inline-flex items-center gap-2.5 rounded-lg border border-border bg-card px-3 py-1.5 shadow-xs">
                            <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                Periode:
                            </span>
                            <div className="w-56">
                                <FilterDate
                                    from={filters.date_from}
                                    to={filters.date_to}
                                    onChange={handleDateChange}
                                />
                            </div>
                        </div>
                    </div>

                    {/* [KHUSUS ADMIN VIEW AS / DETAIL MEMBER]: Tombol Kembali */}
                    {targetMember && (
                        <div className="flex justify-end">
                            <button
                                type="button"
                                onClick={handleBack}
                                title="Kembali"
                                aria-label="Kembali ke Rekap Statement"
                                className="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/90 active:scale-98"
                            >
                                <ArrowLeftToLine className="h-3.5 w-3.5" />
                                Kembali
                            </button>
                        </div>
                    )}
                </div>

                {/* 3. Slip Statement Table (2 Kolom) */}
                <div className="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="w-full table-fixed text-sm">
                            <thead className="bg-muted/60 text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                <tr className="border-b border-border">
                                    <th className="w-12 py-3.5 pr-0 pl-6 text-left">
                                        #
                                    </th>
                                    <th className="px-4 py-3.5 text-center">
                                        <span className="inline-block sm:translate-x-30">
                                            Bonus
                                        </span>
                                    </th>
                                    <th className="w-64 px-6 py-3.5 text-right sm:w-72">
                                        Jumlah
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-border">
                                {/* 5 Baris Tipe Bonus dari Enum */}
                                {items.map((item, idx) => (
                                    <tr
                                        key={`bonus-item-${item.type}`}
                                        className="transition-colors hover:bg-muted/40"
                                    >
                                        <td className="w-12 py-3.5 pr-0 pl-6 text-left font-medium text-muted-foreground">
                                            {idx + 1}
                                        </td>
                                        <td className="py-3.5 pr-4 pl-1 text-left">
                                            <span className={item.color}>
                                                {item.label}
                                            </span>
                                        </td>
                                        <td className="px-6 py-3.5 text-right font-medium text-foreground">
                                            {formatCurrency(item.amount)}
                                        </td>
                                    </tr>
                                ))}

                                {/* 1. Total Bonus */}
                                <tr className="border-t border-border bg-muted/20 font-semibold">
                                    <td></td>
                                    <td className="px-4 py-3.5 text-center text-foreground">
                                        <span className="inline-block sm:translate-x-30">
                                            Total Bonus
                                        </span>
                                    </td>
                                    <td className="px-6 py-3.5 text-right text-foreground">
                                        {formatCurrency(totalBonusVal)}
                                    </td>
                                </tr>

                                {/* 2. Total Pajak PPh21 */}
                                <tr className="font-semibold transition-colors hover:bg-muted/40">
                                    <td></td>
                                    <td className="px-4 py-3.5 text-center text-foreground">
                                        <span className="inline-block sm:translate-x-30">
                                            Total Pajak PPh21
                                        </span>
                                    </td>
                                    <td className="px-6 py-3.5 text-right font-semibold text-red-600 dark:text-red-400">
                                        {totalTaxVal > 0
                                            ? `- ${formatCurrency(totalTaxVal)}`
                                            : formatCurrency(0)}
                                    </td>
                                </tr>

                                {/* 3. Total AutoSave */}
                                <tr className="font-semibold transition-colors hover:bg-muted/40">
                                    <td></td>
                                    <td className="px-4 py-3.5 text-center text-foreground">
                                        <span className="inline-block sm:translate-x-30">
                                            Total AutoSave
                                        </span>
                                    </td>
                                    <td className="px-6 py-3.5 text-right font-semibold text-red-600 dark:text-red-400">
                                        {totalAutoSaveVal > 0
                                            ? `- ${formatCurrency(totalAutoSaveVal)}`
                                            : formatCurrency(0)}
                                    </td>
                                </tr>

                                {/* 4. Total EWC (IN) */}
                                <tr className="border-t-2 border-border bg-primary/10">
                                    <td></td>
                                    <td className="px-4 py-4 text-center text-base font-bold text-primary">
                                        <span className="inline-block sm:translate-x-30">
                                            Total EWC (IN)
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-right text-base font-bold text-green-600 dark:text-green-400">
                                        {formatCurrency(totalEwcVal)}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
}
