import type { InputHTMLAttributes } from 'react';

export type FilterInputProps = Omit<
    InputHTMLAttributes<HTMLInputElement>,
    'onChange' | 'value'
> & {
    value: string;
    onChange: (value: string) => void;
    align?: 'left' | 'center' | 'right';
};

export function FilterInput({
    value,
    onChange,
    placeholder = 'Cari...',
    align = 'left',
    className = '',
    ...props
}: FilterInputProps) {
    const alignClass =
        align === 'center'
            ? 'text-center'
            : align === 'right'
              ? 'text-right'
              : 'text-left';

    return (
        <input
            value={value}
            onChange={(e) => onChange(e.target.value)}
            placeholder={placeholder}
            className={`w-full rounded-md border border-border bg-background px-2 py-1.5 text-xs transition-colors outline-none placeholder:text-muted-foreground/60 focus:border-primary focus:ring-1 focus:ring-primary ${alignClass} ${className}`}
            {...props}
        />
    );
}
