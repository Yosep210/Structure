import { RotateCcw } from 'lucide-react';
import type { ButtonHTMLAttributes } from 'react';

export type ResetButtonProps = Omit<
    ButtonHTMLAttributes<HTMLButtonElement>,
    'onClick' | 'type' | 'title' | 'children'
> & {
    show: boolean;
    onReset: () => void;
    label?: string;
};

export function ResetButton({
    show,
    onReset,
    label = 'Reset',
    className = '',
    ...props
}: ResetButtonProps) {
    if (!show) {
        return null;
    }

    return (
        <button
            {...props}
            type="button"
            onClick={onReset}
            className={`inline-flex animate-in cursor-pointer items-center justify-center gap-1 rounded-md border border-border bg-background px-2 py-1.5 text-[10px] font-medium text-foreground transition-colors fade-in-50 zoom-in-95 hover:bg-muted ${className}`}
            title="Reset semua filter, urutan, dan halaman"
        >
            <RotateCcw className="h-3 w-3" />
            <span>{label}</span>
        </button>
    );
}
