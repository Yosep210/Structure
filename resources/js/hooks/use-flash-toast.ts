import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/types/ui';
const toastTypes = ['success', 'info', 'warning', 'error'] as const;

function isFlashToast(value: unknown): value is FlashToast {
    if (typeof value !== 'object' || value === null) {
        return false;
    }

    const candidate = value as Record<string, unknown>;

    return (
        typeof candidate.message === 'string' &&
        typeof candidate.type === 'string' &&
        toastTypes.includes(candidate.type as FlashToast['type'])
    );
}

export function useFlashToast(): void {
    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data: unknown = flash?.toast;

            if (!isFlashToast(data)) {
                return;
            }

            toast[data.type](data.message);
        });
    }, []);
}
