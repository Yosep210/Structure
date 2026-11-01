import {
    Calendar as CalendarIcon,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';
import {
    useCallback,
    useEffect,
    useRef,
    useState,
    type KeyboardEvent as ReactKeyboardEvent,
} from 'react';

export type FilterDateProps = {
    from?: string;
    to?: string;
    onChange: (from: string, to: string) => void;
    placeholder?: string;
    className?: string;
    align?: 'left' | 'center' | 'right';
};

type ViewMode = 'days' | 'months' | 'years';

const MONTH_NAMES = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
];

const MONTH_SHORT_NAMES = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'Mei',
    'Jun',
    'Jul',
    'Agu',
    'Sep',
    'Okt',
    'Nov',
    'Des',
];

const DOW_NAMES = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

const formatDateShort = (dateStr: string): string => {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    if (!y || !m || !d) return dateStr;
    return `${d}/${m}/${y.slice(-2)}`;
};

const toISODate = (d: Date): string => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const parseISODate = (value: string): Date | null => {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

    if (!match) {
        return null;
    }

    const [, yearPart, monthPart, dayPart] = match;
    const year = Number(yearPart);
    const month = Number(monthPart);
    const day = Number(dayPart);
    const date = new Date(year, month - 1, day);

    if (
        date.getFullYear() !== year ||
        date.getMonth() !== month - 1 ||
        date.getDate() !== day
    ) {
        return null;
    }

    return date;
};

export function FilterDate({
    from = '',
    to = '',
    onChange,
    placeholder = 'Pilih Tanggal',
    className = '',
    align = 'right',
}: FilterDateProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [viewMode, setViewMode] = useState<ViewMode>('days');

    const initialDate = parseISODate(from || to) ?? new Date();
    const [viewYear, setViewYear] = useState<number>(initialDate.getFullYear());
    const [viewMonth, setViewMonth] = useState<number>(initialDate.getMonth());

    const [focusedDate, setFocusedDate] = useState(
        () => from || to || toISODate(new Date()),
    );
    const dateButtonRefs = useRef(new Map<string, HTMLButtonElement>());

    const [selectedFrom, setSelectedFrom] = useState<string>(from);
    const [selectedTo, setSelectedTo] = useState<string>(to);
    const [hoverDate, setHoverDate] = useState<string | null>(null);

    const containerRef = useRef<HTMLDivElement>(null);
    const triggerRef = useRef<HTMLButtonElement>(null);

    const closeCalendar = useCallback(() => {
        setIsOpen(false);
        window.requestAnimationFrame(() => triggerRef.current?.focus());
    }, []);

    useEffect(() => {
        setSelectedFrom(from);
        setSelectedTo(to);
        const referenceDate = from || to;

        if (referenceDate) {
            const date = parseISODate(referenceDate);
            setFocusedDate(referenceDate);

            if (date) {
                setViewYear(date.getFullYear());
                setViewMonth(date.getMonth());
            }
        }
    }, [from, to]);

    useEffect(() => {
        if (isOpen && viewMode === 'days') {
            dateButtonRefs.current.get(focusedDate)?.focus();
        }
    }, [focusedDate, isOpen, viewMonth, viewYear, viewMode]);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                // If user selected a starting date without a second date, commit single date
                if (selectedFrom && !selectedTo) {
                    onChange(selectedFrom, selectedFrom);
                } else if (
                    selectedFrom &&
                    selectedTo &&
                    (selectedFrom !== from || selectedTo !== to)
                ) {
                    onChange(selectedFrom, selectedTo);
                }
                setIsOpen(false);
            }
        };

        const handleKeyDown = (event: KeyboardEvent) => {
            if (!isOpen) return;

            if (event.key === 'Escape') {
                setSelectedFrom(from);
                setSelectedTo(to);
                closeCalendar();
            }
        };

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
            document.addEventListener('keydown', handleKeyDown);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [isOpen, selectedFrom, selectedTo, from, to, onChange, closeCalendar]);

    const handleDateClick = (dateStr: string) => {
        if (!selectedFrom || (selectedFrom && selectedTo)) {
            setSelectedFrom(dateStr);
            setSelectedTo('');
        } else {
            let f = selectedFrom;
            let t = dateStr;
            if (f > t) {
                f = dateStr;
                t = selectedFrom;
            }
            setSelectedFrom(f);
            setSelectedTo(t);
            onChange(f, t);
            closeCalendar();
        }
    };

    const handleDateKeyDown = (
        event: ReactKeyboardEvent<HTMLButtonElement>,
        dateStr: string,
    ) => {
        const offsets = {
            ArrowLeft: -1,
            ArrowRight: 1,
            ArrowUp: -7,
            ArrowDown: 7,
        } as const;

        const offset = offsets[event.key as keyof typeof offsets];

        if (offset === undefined) {
            return;
        }

        event.preventDefault();

        const date = parseISODate(dateStr);

        if (!date) {
            return;
        }

        date.setDate(date.getDate() + offset);

        const nextDate = toISODate(date);

        setFocusedDate(nextDate);
        setViewYear(date.getFullYear());
        setViewMonth(date.getMonth());
    };

    const navigateMonth = (offset: number) => {
        const currentDate =
            parseISODate(focusedDate) ?? new Date(viewYear, viewMonth, 1);
        const targetMonth = new Date(
            currentDate.getFullYear(),
            currentDate.getMonth() + offset,
            1,
        );
        const lastDay = new Date(
            targetMonth.getFullYear(),
            targetMonth.getMonth() + 1,
            0,
        ).getDate();
        const targetDate = new Date(
            targetMonth.getFullYear(),
            targetMonth.getMonth(),
            Math.min(currentDate.getDate(), lastDay),
        );

        setFocusedDate(toISODate(targetDate));
        setViewYear(targetMonth.getFullYear());
        setViewMonth(targetMonth.getMonth());
    };

    const handlePrev = () => {
        if (viewMode === 'days') {
            navigateMonth(-1);
        } else if (viewMode === 'months') {
            setViewYear((y) => y - 1);
        } else if (viewMode === 'years') {
            setViewYear((y) => y - 10);
        }
    };

    const handleNext = () => {
        if (viewMode === 'days') {
            navigateMonth(1);
        } else if (viewMode === 'months') {
            setViewYear((y) => y + 1);
        } else if (viewMode === 'years') {
            setViewYear((y) => y + 10);
        }
    };

    // Generate Calendar Days (42 cells: prev month trailing + current + next)
    const calendarDays = (() => {
        const firstDayOfMonth = new Date(viewYear, viewMonth, 1);
        const startDayOfWeek = firstDayOfMonth.getDay(); // 0 = Sunday
        const lastDateOfMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const lastDateOfPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

        const cells: {
            dateStr: string;
            dayNum: number;
            type: 'old' | 'current' | 'new';
        }[] = [];

        // Trailing days from previous month
        for (let i = startDayOfWeek - 1; i >= 0; i--) {
            const dayNum = lastDateOfPrevMonth - i;
            const prevMonthDate = new Date(viewYear, viewMonth - 1, dayNum);
            cells.push({
                dateStr: toISODate(prevMonthDate),
                dayNum,
                type: 'old',
            });
        }

        // Current month days
        for (let d = 1; d <= lastDateOfMonth; d++) {
            const curDate = new Date(viewYear, viewMonth, d);
            cells.push({
                dateStr: toISODate(curDate),
                dayNum: d,
                type: 'current',
            });
        }

        // Leading days from next month to fill 42 cells (6 rows)
        const remaining = 42 - cells.length;
        for (let d = 1; d <= remaining; d++) {
            const nextDate = new Date(viewYear, viewMonth + 1, d);
            cells.push({
                dateStr: toISODate(nextDate),
                dayNum: d,
                type: 'new',
            });
        }

        return cells;
    })();

    const decadeStart = Math.floor(viewYear / 10) * 10;
    const decadeYears = Array.from(
        { length: 12 },
        (_, i) => decadeStart - 1 + i,
    );

    const isFilled = Boolean(from || to);
    let displayLabel = placeholder;
    if (from && to) {
        displayLabel =
            from === to
                ? formatDateShort(from)
                : `${formatDateShort(from)} - ${formatDateShort(to)}`;
    } else if (from) {
        displayLabel = `≥ ${formatDateShort(from)}`;
    } else if (to) {
        displayLabel = `≤ ${formatDateShort(to)}`;
    }

    const todayStr = toISODate(new Date());

    return (
        <div
            ref={containerRef}
            className={`relative inline-block w-full ${className}`}
        >
            <div className="flex items-center">
                <button
                    type="button"
                    ref={triggerRef}
                    aria-expanded={isOpen}
                    aria-haspopup="dialog"
                    onClick={() => {
                        if (isOpen) {
                            closeCalendar();
                        } else {
                            setIsOpen(true);
                            setViewMode('days');
                        }
                    }}
                    className={`flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-md border px-2 py-1.5 text-xs transition-colors outline-none ${
                        isFilled
                            ? 'border-primary bg-primary/10 font-medium text-foreground'
                            : 'border-border bg-background text-muted-foreground hover:bg-muted/50'
                    }`}
                >
                    <CalendarIcon className="h-3.5 w-3.5 shrink-0 text-primary" />
                    <span className="truncate">{displayLabel}</span>
                </button>
            </div>

            {isOpen && (
                <div
                    role="dialog"
                    aria-label="Pilih rentang tanggal"
                    className={`absolute ${
                        align === 'center'
                            ? 'left-1/2 -translate-x-1/2'
                            : align === 'right'
                              ? 'right-0'
                              : 'left-0'
                    } top-full z-50 mt-1.5 w-65 animate-in rounded-lg border border-border bg-card p-2.5 text-card-foreground shadow-2xl fade-in-0 zoom-in-95`}
                >
                    {/* Header: « Month Year » */}
                    <div className="mb-1.5 flex items-center justify-between">
                        <button
                            type="button"
                            onClick={handlePrev}
                            className="inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded border border-border text-xs text-foreground transition-colors hover:bg-muted"
                            title="Sebelumnya"
                        >
                            <ChevronLeft className="h-3.5 w-3.5" />
                        </button>

                        <button
                            type="button"
                            onClick={() => {
                                if (viewMode === 'days') setViewMode('months');
                                else if (viewMode === 'months')
                                    setViewMode('years');
                                else setViewMode('days');
                            }}
                            className="cursor-pointer rounded px-2 py-0.5 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
                        >
                            {viewMode === 'days' &&
                                `${MONTH_NAMES[viewMonth]} ${viewYear}`}
                            {viewMode === 'months' && `${viewYear}`}
                            {viewMode === 'years' &&
                                `${decadeStart} - ${decadeStart + 9}`}
                        </button>

                        <button
                            type="button"
                            onClick={handleNext}
                            className="inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded border border-border text-xs text-foreground transition-colors hover:bg-muted"
                            title="Selanjutnya"
                        >
                            <ChevronRight className="h-3.5 w-3.5" />
                        </button>
                    </div>

                    {/* 1. DAYS VIEW */}
                    {viewMode === 'days' && (
                        <div className="select-none">
                            {/* Days of week */}
                            <div className="mb-1 grid grid-cols-7 text-center text-[10px] font-semibold text-muted-foreground">
                                {DOW_NAMES.map((dow) => (
                                    <div key={dow} className="py-0.5">
                                        {dow}
                                    </div>
                                ))}
                            </div>

                            {/* Day Cells Grid */}
                            <div className="grid grid-cols-7 gap-y-0.5 text-center text-[11px]">
                                {calendarDays.map(
                                    ({ dateStr, dayNum, type }) => {
                                        const isStart =
                                            selectedFrom === dateStr;
                                        const isEnd = selectedTo === dateStr;

                                        const effectiveTo =
                                            selectedTo ||
                                            (selectedFrom &&
                                            hoverDate &&
                                            hoverDate >= selectedFrom
                                                ? hoverDate
                                                : '');
                                        const inRange =
                                            selectedFrom &&
                                            effectiveTo &&
                                            dateStr >= selectedFrom &&
                                            dateStr <= effectiveTo;

                                        const isToday = dateStr === todayStr;

                                        let cellBg = '';
                                        let cellText =
                                            type === 'current'
                                                ? 'text-foreground'
                                                : 'text-muted-foreground/35';

                                        if (isStart || isEnd) {
                                            cellBg =
                                                'bg-primary text-primary-foreground font-semibold';
                                            cellText =
                                                'text-primary-foreground';
                                        } else if (inRange) {
                                            cellBg =
                                                'bg-primary/15 text-primary font-medium';
                                        }

                                        return (
                                            <button
                                                aria-label={dateStr}
                                                key={dateStr}
                                                type="button"
                                                ref={(element) => {
                                                    if (element) {
                                                        dateButtonRefs.current.set(
                                                            dateStr,
                                                            element,
                                                        );
                                                    } else {
                                                        dateButtonRefs.current.delete(
                                                            dateStr,
                                                        );
                                                    }
                                                }}
                                                tabIndex={
                                                    dateStr === focusedDate
                                                        ? 0
                                                        : -1
                                                }
                                                onKeyDown={(event) =>
                                                    handleDateKeyDown(
                                                        event,
                                                        dateStr,
                                                    )
                                                }
                                                onClick={() =>
                                                    handleDateClick(dateStr)
                                                }
                                                onMouseEnter={() =>
                                                    setHoverDate(dateStr)
                                                }
                                                className={`relative flex h-6 w-full cursor-pointer items-center justify-center text-[11px] transition-colors ${
                                                    isStart && selectedTo
                                                        ? 'rounded-l'
                                                        : isEnd
                                                          ? 'rounded-r'
                                                          : inRange
                                                            ? ''
                                                            : 'rounded'
                                                } ${cellBg} ${cellText} ${
                                                    !inRange &&
                                                    !isStart &&
                                                    !isEnd
                                                        ? 'hover:bg-muted'
                                                        : ''
                                                } ${
                                                    isToday &&
                                                    !isStart &&
                                                    !isEnd
                                                        ? 'font-bold ring-1 ring-primary/40'
                                                        : ''
                                                }`}
                                            >
                                                {dayNum}
                                            </button>
                                        );
                                    },
                                )}
                            </div>
                        </div>
                    )}

                    {/* 2. MONTHS VIEW */}
                    {viewMode === 'months' && (
                        <div className="grid grid-cols-3 gap-1.5 py-1 select-none">
                            {MONTH_SHORT_NAMES.map((mName, idx) => {
                                const isCurrent =
                                    viewMonth === idx &&
                                    viewYear === new Date().getFullYear();
                                return (
                                    <button
                                        key={mName}
                                        type="button"
                                        onClick={() => {
                                            setViewMonth(idx);
                                            setViewMode('days');
                                        }}
                                        className={`cursor-pointer rounded py-2 text-xs font-medium transition-colors ${
                                            isCurrent
                                                ? 'bg-primary font-semibold text-primary-foreground'
                                                : 'text-foreground hover:bg-muted'
                                        }`}
                                    >
                                        {mName}
                                    </button>
                                );
                            })}
                        </div>
                    )}

                    {/* 3. YEARS VIEW */}
                    {viewMode === 'years' && (
                        <div className="grid grid-cols-3 gap-1.5 py-1 select-none">
                            {decadeYears.map((yr, idx) => {
                                const isOut = idx === 0 || idx === 11;
                                const isCurrent = yr === viewYear;
                                return (
                                    <button
                                        key={yr}
                                        type="button"
                                        onClick={() => {
                                            setViewYear(yr);
                                            setViewMode('months');
                                        }}
                                        className={`cursor-pointer rounded py-2 text-xs font-medium transition-colors ${
                                            isCurrent
                                                ? 'bg-primary font-semibold text-primary-foreground'
                                                : isOut
                                                  ? 'text-muted-foreground/40 hover:bg-muted'
                                                  : 'text-foreground hover:bg-muted'
                                        }`}
                                    >
                                        {yr}
                                    </button>
                                );
                            })}
                        </div>
                    )}

                    {/* Quick action footer */}
                    <div className="mt-2 flex items-center justify-between border-t border-border pt-2 text-[11px]">
                        <button
                            type="button"
                            onClick={() => {
                                onChange(todayStr, todayStr);
                                closeCalendar();
                            }}
                            className="cursor-pointer rounded px-2 py-1 font-medium text-primary transition-colors hover:bg-primary/10"
                        >
                            Hari Ini
                        </button>
                        {isFilled && (
                            <button
                                type="button"
                                onClick={() => {
                                    setSelectedFrom('');
                                    setSelectedTo('');
                                    onChange('', '');
                                    closeCalendar();
                                }}
                                className="cursor-pointer rounded px-2 py-1 font-medium text-muted-foreground transition-colors hover:bg-muted"
                            >
                                Hapus
                            </button>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

export const FilterDateRangePicker = FilterDate;
export type FilterDateRangePickerProps = FilterDateProps;
