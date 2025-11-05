import { NavFooter } from '@/components/nav-footer';
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
import { dashboard } from '@/routes';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, type LucideIcon } from 'lucide-react';
import * as LucideIcons from 'lucide-react';
import AppLogo from './app-logo';

// Helper function to dynamically get any Lucide icon component from string name
// No need to manually add icons - works with any valid Lucide icon name!
const getIconComponent = (iconName?: string): LucideIcon => {
    if (!iconName) return Folder;
    
    // Dynamically access the icon from lucide-react
    const IconComponent = (LucideIcons as any)[iconName];
    
    // Fallback to Folder icon if icon name is invalid
    return IconComponent || Folder;
};

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/thopd88/ishub',
        icon: Folder,
    },
];

export function AppSidebar() {
    const { modules } = usePage<SharedData>().props;

    // Build main navigation items with Dashboard first, then modules
    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...modules.map((module) => ({
            ...module,
            icon: getIconComponent(module.icon as any),
        })),
    ];

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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
