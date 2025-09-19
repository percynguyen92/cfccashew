import bills from '@/routes/bills';
import containers from '@/routes/containers';
import cuttingTests from '@/routes/cutting-tests';
import { dashboard } from '@/routes/index';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useBreadcrumbs() {
    const page = usePage();

    const breadcrumbs = computed<BreadcrumbItem[]>(() => {
        const url = page.url;
        const breadcrumbItems: BreadcrumbItem[] = [];

        // Always start with Dashboard
        breadcrumbItems.push({
            title: 'Dashboard',
            href: dashboard.url(),
        });

        // Add section-specific breadcrumbs
        if (url.startsWith('/bills')) {
            breadcrumbItems.push({
                title: 'Bills',
                href: bills.index.url(),
            });

            if (url === '/bills/create') {
                breadcrumbItems.push({
                    title: 'Create Bill',
                    href: url,
                });
            } else if (url.match(/^\/bills\/\d+-.+$/)) {
                const slug = url.split('/')[2];
                const billNumber = slug.split('-').slice(1).join('-');
                breadcrumbItems.push({
                    title: `Bill ${billNumber}`,
                    href: url,
                });
            } else if (url.match(/^\/bills\/\d+$/)) {
                const billId = url.split('/')[2];
                breadcrumbItems.push({
                    title: `Bill #${billId}`,
                    href: url,
                });
            }
        } else if (url.startsWith('/containers')) {
            breadcrumbItems.push({
                title: 'Containers',
                href: containers.index.url(),
            });

            if (url.match(/^\/containers\/\d+$/)) {
                const containerId = url.split('/')[2];
                breadcrumbItems.push({
                    title: `Container #${containerId}`,
                    href: url,
                });
            }
        } else if (url.startsWith('/cutting-tests')) {
            breadcrumbItems.push({
                title: 'Cutting Tests',
                href: cuttingTests.index.url(),
            });
        }

        return breadcrumbItems;
    });

    return {
        breadcrumbs,
    };
}
