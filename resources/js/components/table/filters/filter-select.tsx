import type { SelectHTMLAttributes } from 'react';

export type FilterSelectOption = {
    label: string;
    value: string;
};

export type FilterSelectProps = Omit<
    SelectHTMLAttributes<HTMLSelectElement>,
    'onChange' | 'value' | 'defaultValue' | 'children' | 'multiple'
> & {
    value: string;
    onChange: (value: string) => void;
    options: FilterSelectOption[];
    placeholder?: string;
};

export function FilterSelect({
    value,
    onChange,
    options,
    placeholder = 'Pilih',
    className = '',
    ...props
}: FilterSelectProps) {
    return (
        <select
            value={value}
            onChange={(e) => onChange(e.target.value)}
            className={`w-full cursor-pointer rounded-md border border-border bg-background px-2 py-1.5 text-xs transition-colors outline-none focus:border-primary focus:ring-1 focus:ring-primary ${className}`}
            {...props}
        >
            {placeholder ? <option value="">{placeholder}</option> : null}
            {options.map((opt) => (
                <option key={opt.value} value={opt.value}>
                    {opt.label}
                </option>
            ))}
        </select>
    );
}
