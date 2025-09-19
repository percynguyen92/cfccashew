import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Bill, Container, and CuttingTest interfaces
export interface Bill {
    id: number;
    slug: string;
    bill_number: string | null;
    seller: string | null;
    buyer: string | null;
    note: string | null;
    containers_count?: number;
    final_samples_count?: number;
    average_outurn?: number | null;
    containers?: Container[];
    final_samples?: CuttingTest[];
    cutting_tests?: CuttingTest[];
    created_at: string;
    updated_at: string;
}

export interface Container {
    id: number;
    bill_id: number;
    truck: string | null;
    container_number: string | null;
    quantity_of_bags: number | null;
    w_jute_bag: number;
    w_total: number | null;
    w_truck: number | null;
    w_container: number | null;
    w_gross: number | null;
    w_dunnage_dribag: number | null;
    w_tare: number | null;
    w_net: number | null;
    note: string | null;
    average_moisture?: number | null;
    outturn_rate?: number | null;
    bill?: Bill;
    cutting_tests?: CuttingTest[];
    created_at: string;
    updated_at: string;
}

export interface CuttingTest {
    id: number;
    bill_id: number;
    container_id: number | null;
    type: 1 | 2 | 3 | 4;
    type_label: string;
    moisture: number | null;
    sample_weight: number;
    nut_count: number | null;
    w_reject_nut: number | null;
    w_defective_nut: number | null;
    w_defective_kernel: number | null;
    w_good_kernel: number | null;
    w_sample_after_cut: number | null;
    outturn_rate: number | null;
    note: string | null;
    defective_ratio?: {
        defective_nut: number;
        defective_kernel: number;
        ratio: number;
        formatted: string;
    };
    is_final_sample: boolean;
    is_container_test: boolean;
    bill?: Bill;
    container?: Container;
    created_at: string;
    updated_at: string;
}
