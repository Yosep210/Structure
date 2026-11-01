import type { ReactNode } from 'react';
import type { LucideIcon } from 'lucide-react';
import { FilterDate } from './filters/filter-date';
import { FilterInput } from './filters/filter-input';
import { FilterRange } from './filters/filter-range';
import { FilterSelect, type FilterSelectOption } from './filters/filter-select';
import { ResetButton } from './filters/reset-button';
import { SortHeader } from './sorts/sort-header';
import { TableEmpty } from './table-empty';

export type DataTableFilter<F extends Record<string, string>> =
    | { type: 'input'; key: keyof F }
    | {
          type: 'select';
          key: keyof F;
          options: FilterSelectOption[];
      }
    | {
          type: 'date';
          fromKey: keyof F;
          toKey: keyof F;
      }
    | {
          type: 'range';
          minKey: keyof F;
          maxKey: keyof F;
      };

export type DataTableColumn<
    F extends Record<string, string> = Record<string, string>,
> = {
    label: ReactNode;
    sortKey?: string;
    filter?: DataTableFilter<F>;
    align?: 'left' | 'center' | 'right';
    headerClassName?: string;
};

export type SortableFilterableColumn<F extends Record<string, string>> = Omit<
    DataTableColumn<F>,
    'sortKey' | 'filter'
> & {
    sortKey: string;
    filter: DataTableFilter<F>;
};

type DataTableProps<T, F extends Record<string, string>> = {
    rows: T[];
    getRowKey: (row: T) => React.Key;
    columns: DataTableColumn<F>[];
    filters: F;
    onFilterChange: (key: keyof F, value: string) => void;
    onFiltersChange: (filters: Partial<F>) => void;
    sortKey: string;
    sortDirection: 'asc' | 'desc';
    onSort: (key: string) => void;
    showReset: boolean;
    onReset: () => void;
    rowNumberStart: number;
    renderRow: (row: T, index: number) => ReactNode;
    emptyMessage?: string;
    emptyIcon?: LucideIcon;
};

export function DataTable<T, F extends Record<string, string>>({
    rows,
    getRowKey,
    columns,
    filters,
    onFilterChange,
    onFiltersChange,
    sortKey,
    sortDirection,
    onSort,
    showReset,
    onReset,
    rowNumberStart,
    renderRow,
    emptyMessage = 'Belum ada data.',
    emptyIcon,
}: DataTableProps<T, F>) {
    const hasFilterControls = columns.some((column) => column.filter);
    const showFilterRow = hasFilterControls || showReset;

    const renderFilter = (filter: DataTableFilter<F>) => {
        if (filter.type === 'input') {
            return (
                <FilterInput
                    value={String(filters[filter.key] ?? '')}
                    onChange={(value) => onFilterChange(filter.key, value)}
                />
            );
        }

        if (filter.type === 'select') {
            return (
                <FilterSelect
                    value={String(filters[filter.key] ?? '')}
                    options={filter.options}
                    onChange={(value) => onFilterChange(filter.key, value)}
                />
            );
        }

        if (filter.type === 'date') {
            return (
                <FilterDate
                    from={String(filters[filter.fromKey] ?? '')}
                    to={String(filters[filter.toKey] ?? '')}
                    onChange={(from, to) =>
                        onFiltersChange({
                            [filter.fromKey]: from,
                            [filter.toKey]: to,
                        } as Partial<F>)
                    }
                />
            );
        }

        if (filter.type === 'range') {
            return (
                <FilterRange
                    min={String(filters[filter.minKey] ?? '')}
                    max={String(filters[filter.maxKey] ?? '')}
                    onChangeMin={(value) =>
                        onFilterChange(filter.minKey, value)
                    }
                    onChangeMax={(value) =>
                        onFilterChange(filter.maxKey, value)
                    }
                />
            );
        }
    };

    return (
        <table className="w-full text-sm">
            <thead className="bg-muted/60 text-center text-xs tracking-wider text-muted-foreground uppercase">
                <tr>
                    <th className="px-4 py-3 text-center" scope="col">
                        #
                    </th>
                    {columns.map((column, index) =>
                        column.sortKey ? (
                            <SortHeader
                                key={column.sortKey}
                                columnKey={column.sortKey}
                                label={column.label}
                                activeSortKey={sortKey}
                                direction={sortDirection}
                                onSort={onSort}
                                align={column.align}
                                className={column.headerClassName}
                            />
                        ) : (
                            <th
                                key={`column-${index}`}
                                className={`px-4 py-3 text-xs font-semibold tracking-wider text-muted-foreground uppercase ${column.align === 'center' ? 'text-center' : column.align === 'right' ? 'text-right' : 'text-left'} ${column.headerClassName ?? ''}`}
                            >
                                {column.label}
                            </th>
                        ),
                    )}
                    <th className="px-4 py-3 text-center" scope="col">
                        Aksi
                    </th>
                </tr>
                {showFilterRow && (
                    <tr className="border-t border-border bg-muted/20">
                        <th scope="col" />
                        {columns.map((column, index) => (
                            <th
                                key={`filter-${index}`}
                                className={`px-2 py-2 ${column.filter?.type === 'date' || column.filter?.type === 'range' ? 'min-w-37.5 text-left' : 'text-left'}`}
                                scope="col"
                            >
                                {column.filter
                                    ? renderFilter(column.filter)
                                    : null}
                            </th>
                        ))}
                        <th className="px-2 py-2 text-center" scope="col">
                            <ResetButton show={showReset} onReset={onReset} />
                        </th>
                    </tr>
                )}
            </thead>
            <tbody className="divide-y divide-border">
                {rows.length === 0 ? (
                    <TableEmpty
                        colSpan={columns.length + 2}
                        message={emptyMessage}
                        icon={emptyIcon}
                    />
                ) : (
                    rows.map((row, index) => (
                        <tr key={getRowKey(row)}>
                            <td className="px-4 py-3 text-center text-muted-foreground">
                                {rowNumberStart + index}
                            </td>
                            {renderRow(row, index)}
                        </tr>
                    ))
                )}
            </tbody>
        </table>
    );
}
