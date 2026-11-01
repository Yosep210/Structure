import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { adminMenu, memberMenu } from '@/config/menu';
import { dashboard } from '@/routes';

export function AppSidebar() {
    const { auth } = usePage().props;
    const isAdmin =
        auth.user.roles?.some((role) =>
            typeof role === 'string' ? role === 'admin' : role.name === 'admin',
        ) ?? false;

    const menu = isAdmin ? adminMenu : memberMenu;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain groups={menu} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
