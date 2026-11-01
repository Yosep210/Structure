import type { HTMLAttributes } from 'react';

export type FilterRangeProps = Omit<
    HTMLAttributes<HTMLDivElement>,
    'onChange'
> & {
    min: string | number;
    max: string | number;
    onChangeMin: (val: string) => void;
    onChangeMax: (val: string) => void;
    placeholderMin?: string;
    placeholderMax?: string;
    inputMode?: 'numeric' | 'text' | 'decimal';
    pattern?: string;
    align?: 'left' | 'center' | 'right';
    separator?: string;
};

export function FilterRange({
    min,
    max,
    onChangeMin,
    onChangeMax,
    placeholderMin = 'Min',
    placeholderMax = 'Max',
    inputMode = 'numeric',
    pattern = '[0-9]*',
    align = 'center',
    separator = '-',
    className = '',
    ...props
}: FilterRangeProps) {
    const alignClass =
        align === 'center'
            ? 'text-center'
            : align === 'right'
              ? 'text-right'
              : 'text-left';

    const baseInputClass = `w-full min-w-0 rounded-md border border-border bg-background px-1.5 py-1.5 text-xs outline-none transition-colors placeholder:text-muted-foreground/60 focus:border-primary focus:ring-1 focus:ring-primary [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none ${alignClass}`;

    const handleNumericChange = (
        val: string,
        onChange: (v: string) => void,
    ) => {
        if (inputMode === 'numeric') {
            const cleanVal = val.replace(/[^0-9]/g, '');
            onChange(cleanVal);
        } else {
            onChange(val);
        }
    };

    return (
        <div
            className={`flex w-full min-w-32.5 items-center gap-1 ${className}`}
            {...props}
        >
            <input
                inputMode={inputMode}
                pattern={pattern}
                value={min ?? ''}
                onChange={(e) =>
                    handleNumericChange(e.target.value, onChangeMin)
                }
                placeholder={placeholderMin}
                className={baseInputClass}
            />
            <span className="shrink-0 text-xs font-semibold text-muted-foreground/70 select-none">
                {separator}
            </span>
            <input
                inputMode={inputMode}
                pattern={pattern}
                value={max ?? ''}
                onChange={(e) =>
                    handleNumericChange(e.target.value, onChangeMax)
                }
                placeholder={placeholderMax}
                className={baseInputClass}
            />
        </div>
    );
}

export const FilterMinMax = FilterRange;
export type FilterMinMaxProps = FilterRangeProps;
