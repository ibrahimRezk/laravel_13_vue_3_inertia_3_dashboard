import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import type { RouteDefinition } from '@/wayfinder';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

// export type NavItem = {
//     title: string;
//     href: NonNullable<InertiaLinkProps['href']>;
//     icon?: LucideIcon;
//     isActive?: boolean;
// };

export interface SubMenuItem { // new
    title: string;
    url?: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean; 
    isVisible?: boolean; 
}
export interface NavItem { // new
    title: string;
    url?: string;
    href: string | RouteDefinition<"get">;
    icon?: LucideIcon;
    isActive?: boolean; 
    isVisible?: boolean; 
    hasSubmenu?: boolean; 
    open?: boolean; 
    subMenus?: SubMenuItem[];  
}

