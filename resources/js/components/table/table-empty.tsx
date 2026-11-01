import { type LucideIcon, Inbox } from 'lucide-react';

type TableEmptyProps = {
    colSpan: number;
    message?: string;
    icon?: LucideIcon;
};

export function TableEmpty({
    colSpan,
    message = 'Belum ada data ditemukan.',
    icon: Icon = Inbox,
}: TableEmptyProps) {
    return (
        <tr>
            <td
                colSpan={colSpan}
                className="px-5 py-12 text-center text-muted-foreground"
            >
                <div className="flex flex-col items-center justify-center gap-2">
                    <Icon
                        className="h-8 w-8 text-muted-foreground/50"
                        aria-hidden="true"
                    />
                    <p className="font-medium">{message}</p>
                </div>
            </td>
        </tr>
    );
}
