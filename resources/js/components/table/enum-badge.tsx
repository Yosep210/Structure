export type Option = {
    label: string;
    value: string;
    color?: string;
};

export type EnumBadgeProps = {
    options?: Option[];
    color?: string;
    value?: string | number | null;
    placeholder?: string;
};

export function EnumBadge({
    options,
    color = 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset text-rose-700 dark:text-rose-400',
    value,
    placeholder = '-',
}: EnumBadgeProps) {
    if (value === undefined || value === null || value === '')
        return <>{placeholder}</>;
    const item = options?.find((o) => String(o.value) === String(value));
    if (!item) return <>{placeholder}</>;
    return <span className={item.color || color}>{item.label}</span>;
}
