import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useEffect, useState } from 'react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavGroup, NavItem, NavRoute, NavSubItem } from '@/types';

export function NavMain({ groups }: { groups: NavGroup[] }) {
    const { currentUrl, isCurrentOrParentUrl } = useCurrentUrl();
    const { state, isMobile } = useSidebar();
    const isCollapsed = state === 'collapsed' && !isMobile;

    const isHrefActive = (href: NavRoute, activeRoutes: NavRoute[] = []) => {
        return (
            isCurrentOrParentUrl(href) ||
            activeRoutes.some((route) => isCurrentOrParentUrl(route))
        );
    };

    const isSubItemActive = (subItem: NavSubItem) => {
        if (subItem.isActive !== undefined) return subItem.isActive;
        return isHrefActive(subItem.href, subItem.activeRoutes);
    };

    const isItemActive = (item: NavItem) => {
        if (item.isActive !== undefined) return item.isActive;
        if (item.href) {
            return isHrefActive(item.href, item.activeRoutes);
        }
        if (item.items && item.items.length > 0) {
            return item.items.some(isSubItemActive);
        }
        return false;
    };

    return (
        <>
            {groups.map((group, groupIdx) => {
                if (group.items.length === 0) return null;

                return (
                    <SidebarGroup
                        key={group.label ?? `group-${groupIdx}`}
                        className="px-2 py-0"
                    >
                        {group.label && (
                            <SidebarGroupLabel>{group.label}</SidebarGroupLabel>
                        )}
                        <SidebarMenu>
                            {group.items.map((item) => {
                                const hasSubItems = Boolean(
                                    item.items && item.items.length > 0,
                                );
                                const active = isItemActive(item);

                                if (hasSubItems) {
                                    // Mode Icon / Sidebar Ciut: Tampilkan Flyout Dropdown Popover
                                    if (isCollapsed) {
                                        return (
                                            <NavCollapsedItem
                                                key={item.title}
                                                item={item}
                                                active={active}
                                                isSubItemActive={
                                                    isSubItemActive
                                                }
                                            />
                                        );
                                    }

                                    // Mode Normal / Expanded: Tampilkan Collapsible Accordion
                                    return (
                                        <NavCollapsibleItem
                                            key={item.title}
                                            item={item}
                                            active={active}
                                            isSubItemActive={isSubItemActive}
                                            currentUrl={currentUrl}
                                        />
                                    );
                                }

                                return (
                                    <SidebarMenuItem key={item.title}>
                                        <SidebarMenuButton
                                            asChild={Boolean(item.href)}
                                            isActive={active}
                                            tooltip={{ children: item.title }}
                                        >
                                            {item.href ? (
                                                <Link href={item.href} prefetch>
                                                    {item.icon && <item.icon />}
                                                    <span>{item.title}</span>
                                                </Link>
                                            ) : (
                                                <span className="flex items-center gap-2">
                                                    {item.icon && <item.icon />}
                                                    <span>{item.title}</span>
                                                </span>
                                            )}
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                );
                            })}
                        </SidebarMenu>
                    </SidebarGroup>
                );
            })}
        </>
    );
}

function NavCollapsedItem({
    item,
    active,
    isSubItemActive,
}: {
    item: NavItem;
    active: boolean;
    isSubItemActive: (subItem: NavSubItem) => boolean;
}) {
    return (
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <SidebarMenuButton
                        tooltip={{
                            children: item.title,
                        }}
                        isActive={active}
                    >
                        {item.icon && <item.icon />}
                        <span>{item.title}</span>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    side="right"
                    align="start"
                    sideOffset={8}
                    className="min-w-48"
                >
                    <DropdownMenuLabel>{item.title}</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {item.items?.map((subItem) => {
                        const subActive = isSubItemActive(subItem);
                        return (
                            <DropdownMenuItem key={subItem.title} asChild>
                                <Link
                                    href={subItem.href}
                                    prefetch
                                    className={`flex w-full cursor-pointer items-center gap-2 ${
                                        subActive
                                            ? 'bg-primary/10 font-semibold text-primary'
                                            : ''
                                    }`}
                                >
                                    {subItem.icon && (
                                        <subItem.icon className="h-4 w-4" />
                                    )}
                                    <span>{subItem.title}</span>
                                </Link>
                            </DropdownMenuItem>
                        );
                    })}
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    );
}

function NavCollapsibleItem({
    item,
    active,
    isSubItemActive,
    currentUrl,
}: {
    item: NavItem;
    active: boolean;
    isSubItemActive: (subItem: NavSubItem) => boolean;
    currentUrl: string;
}) {
    const [open, setOpen] = useState(active);

    useEffect(() => {
        setOpen(active);
    }, [currentUrl, active]);

    return (
        <Collapsible
            open={open}
            onOpenChange={setOpen}
            className="group/collapsible"
        >
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton
                        tooltip={{
                            children: item.title,
                        }}
                        isActive={active}
                    >
                        {item.icon && <item.icon />}
                        <span>{item.title}</span>
                        <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub className="border-none">
                        {item.items?.map((subItem) => (
                            <SidebarMenuSubItem key={subItem.title}>
                                <SidebarMenuSubButton
                                    asChild
                                    isActive={isSubItemActive(subItem)}
                                >
                                    <Link href={subItem.href} prefetch>
                                        {subItem.icon && <subItem.icon />}
                                        <span>{subItem.title}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}
