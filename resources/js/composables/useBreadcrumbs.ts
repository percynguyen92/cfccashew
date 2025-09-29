import bills from '@/routes/bills';
import containers from '@/routes/containers';
import cuttingTests from '@/routes/cutting-tests';
import { dashboard } from '@/routes/index';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

export function useBreadcrumbs() {
    const page = usePage();
    const { t } = useI18n();

    const breadcrumbs = computed<BreadcrumbItem[]>(() => {
        const url = page.url;
        const breadcrumbItems: BreadcrumbItem[] = [];

        // Always start with Dashboard
        breadcrumbItems.push({
            title: t('navigation.dashboard'),
            href: dashboard.url(),
        });

        // Add section-specific breadcrumbs
        if (url.startsWith('/bills')) {
            breadcrumbItems.push({
                title: t('navigation.bills'),
                href: bills.index.url(),
            });

            if (url === '/bills/create') {
                breadcrumbItems.push({
                    title: t('breadcrumbs.bills.create'),
                    href: url,
                });
            } else if (url.match(/^\/bills\/\d+-.+$/)) {
                const slug = (url.split('/')[2] ?? '').split('?')[0];
                const [billId = '', ...billNumberParts] = slug.split('-');
                const billNumber = billNumberParts.join('-');
                breadcrumbItems.push({
                    title: billNumber
                        ? t('breadcrumbs.bills.showWithNumber', {
                              id: billId,
                              number: billNumber,
                          })
                        : t('breadcrumbs.bills.show', { id: billId }),
                    href: url,
                });
            } else if (url.match(/^\/bills\/\d+$/)) {
                const billId = (url.split('/')[2] ?? '').split('?')[0];
                breadcrumbItems.push({
                    title: t('breadcrumbs.bills.show', { id: billId }),
                    href: url,
                });
            }
        } else if (url.startsWith('/containers')) {
            breadcrumbItems.push({
                title: t('navigation.containers'),
                href: containers.index.url(),
            });

            // Match container number format (4 letters + 7 digits) or numeric ID
            if (url.match(/^\/containers\/[A-Z]{4}\d{7}$/)) {
                const containerNumber = url.split('/')[2];
                breadcrumbItems.push({
                    title: t('breadcrumbs.containers.showNumber', {
                        number: containerNumber,
                    }),
                    href: url,
                });
            } else if (url.match(/^\/containers\/\d+$/)) {
                const containerId = url.split('/')[2];
                breadcrumbItems.push({
                    title: t('breadcrumbs.containers.showId', {
                        id: containerId,
                    }),
                    href: url,
                });
            }
        } else if (url.startsWith('/cutting-tests')) {
            breadcrumbItems.push({
                title: t('navigation.cuttingTests'),
                href: cuttingTests.index.url(),
            });
        }

        return breadcrumbItems;
    });

    return {
        breadcrumbs,
    };
}
