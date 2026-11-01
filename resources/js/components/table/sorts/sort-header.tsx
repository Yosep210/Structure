import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import type { HTMLAttributes, ReactNode } from 'react';

export type SortHeaderProps = Omit<
    HTMLAttributes<HTMLTableCellElement>,
    'children' | 'aria-sort'
> & {
    columnKey: string;
    activeSortKey: string;
    label: ReactNode;
    direction: 'asc' | 'desc';
    onSort: (columnKey: string) => void;
    align?: 'left' | 'center' | 'right';
};

export function SortHeader(props: SortHeaderProps) {
    const {
        columnKey,
        activeSortKey,
        label,
        direction,
        onSort,
        align = 'center',
        className = '',
        ...tableCellProps
    } = props;
    const isSorted = activeSortKey === columnKey;

    const alignClass =
        align === 'center'
            ? 'justify-center text-center'
            : align === 'right'
              ? 'justify-end text-right ml-auto'
              : 'justify-start text-left';

    const thAlignClass =
        align === 'center'
            ? 'text-center'
            : align === 'right'
              ? 'text-right'
              : 'text-left';

    return (
        <th
            scope="col"
            aria-sort={
                isSorted
                    ? direction === 'asc'
                        ? 'ascending'
                        : 'descending'
                    : undefined
            }
            className={`px-4 py-3 text-xs font-semibold tracking-wider text-muted-foreground uppercase ${thAlignClass} ${className}`}
            {...tableCellProps}
        >
            <button
                type="button"
                onClick={() => onSort(columnKey)}
                className={`inline-flex cursor-pointer items-center gap-1.5 font-semibold transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none ${alignClass}`}
            >
                <span>{label}</span>
                {isSorted ? (
                    direction === 'asc' ? (
                        <ArrowUp className="h-3.5 w-3.5 text-primary" />
                    ) : (
                        <ArrowDown className="h-3.5 w-3.5 text-primary" />
                    )
                ) : (
                    <ArrowUpDown className="h-3 w-3 opacity-30 transition-opacity hover:opacity-100" />
                )}
            </button>
        </th>
    );
}
