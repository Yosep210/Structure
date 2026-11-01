import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
} & ({ title: string } | { title?: string; icon: LucideIcon });

export type NavGroup = {
    label?: string;
    items: NavItem[];
};

export type NavRoute = NonNullable<InertiaLinkProps['href']>;

type NavItemBase = {
    title: string;
    href?: NavRoute;
    activeRoutes?: NavRoute[];
    icon?: LucideIcon | null;
    isActive?: boolean;
};

export type NavLinkItem = Omit<NavItemBase, 'href'> & {
    href: NavRoute;
};

export type NavItem = NavItemBase & {
    items?: NavSubItem[];
};

export type NavSubItem = NavLinkItem;
