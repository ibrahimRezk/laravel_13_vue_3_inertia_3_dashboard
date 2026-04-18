import type { RouteDefinition } from '@/wayfinder';
// import type { LucideIcon } from 'lucide-vue-next';



export * from './auth';
export * from './navigation';
export * from './ui';





export interface Auth {
    user: User;
}


export interface BreadcrumbItem {
    title: string | object;
    href: RouteDefinition<'get'> | string;
}


export interface FlashMessages {
    success: string;
    error: string;
}



export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    menus: Record<string, any>[];
    sidebarOpen: boolean;
    paginationNumber: number;
    messages: object;
    errors: object;
};

interface name {
    ar: string,
    en: string
}
export interface User {
    id: number;
    name: name;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}


interface tableHeader {
    name: string,
    label:string
}

export interface Link {
  url: string | null;
  label: string;
  active: boolean;
}

export interface TableProps { // new
  headers?: tableHeader[]; // Ideally, replace 'any' with a specific interface for your header

 items?: Record<string, any> ;
    // items?: Record<string, number | any>;


  headersClasses?: string;
  headerFooterClasses?: string;
  trClasses?: string;
  bodyClasses?: string;
  hoverClasses?: string;
  noCheckAll?: boolean;
  checkedAllButton?: boolean;
  noNamePadding?: boolean;
  withAxios?: boolean;
  noPagination?: boolean;
  has_extra_final_row?: boolean;
  showPaginationNumber?: boolean;
  tableHeight?: string;
}







export interface header {
    name: string;
    label: string;
}

export interface meta {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

export interface links {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
}





export interface fillFormType {
            [key: string]: any;

}

export interface permissions {
    create?: boolean;
    edit?: boolean;
    delete?: boolean;
}


export interface filtersValuesDataType {
    [key: string]: {
        id: string;
        data: string | number;
    };
}







export type BreadcrumbItemType = BreadcrumbItem;
export type FlashMessagesType = FlashMessages;
