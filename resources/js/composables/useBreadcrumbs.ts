import { RouteDefinition } from '@/wayfinder';
import { ref } from 'vue'

export interface  slug {
        ar: string;
        en: string;
}

export interface BreadcrumbItem {
    title: string | object;
    href: RouteDefinition<'get'> | string;
}


const breadcrumbs = ref<BreadcrumbItem[]>([])
export function useBreadcrumbs() {
    return { breadcrumbs  }
}