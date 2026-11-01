import { dashboard, home } from '@/routes';
import { Link, usePage } from '@inertiajs/react';

export default function CopyRight() {
    const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
    const { auth } = usePage().props;
    const destination = auth.user ? dashboard() : home();

    return (
        <div className="text-center text-sm hover:text-zinc-600 hover:dark:text-zinc-400">
            <Link href={destination}>
                &copy; {appName} {new Date().getFullYear()}
            </Link>
        </div>
    );
}
