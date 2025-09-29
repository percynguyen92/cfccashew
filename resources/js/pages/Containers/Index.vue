<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Pagination,
    PaginationList,
    PaginationListItem,
} from '@/components/ui/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import * as containerRoutes from '@/routes/containers';
import type { Container } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import { Pencil, Search, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    containers: Container[];
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: PaginationLink[];
    };
    filters: {
        container_number?: string;
        truck?: string;
        bill_info?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const normalizeFilters = (filters: Props['filters']) => ({
    container_number: filters.container_number ?? '',
    truck: filters.truck ?? '',
    bill_info: filters.bill_info ?? '',
    date_from: filters.date_from ?? '',
    date_to: filters.date_to ?? '',
});

const { t } = useI18n();
const { breadcrumbs } = useBreadcrumbs();
const { goToPage } = usePagination();

const containers = computed(() => props.containers);
const pagination = computed(() => props.pagination);

const isConfirmOpen = ref(false);
const isDeleting = ref(false);
const containerPendingDeletion = ref<Container | null>(null);

const pendingContainerLabel = computed(() => {
    const container = containerPendingDeletion.value;

    if (!container) return '';

    return (
        container.container_number ||
        t('containers.index.dialog.delete.fallback', { id: container.id })
    );
});

const searchForm = ref(normalizeFilters(props.filters));

watch(
    () => props.filters,
    (newFilters) => {
        searchForm.value = normalizeFilters(newFilters);
    },
    { deep: true },
);

const cleanedFilters = () => {
    const filters = { ...searchForm.value } as Record<string, string>;

    Object.keys(filters).forEach((key) => {
        if (!filters[key]) {
            delete filters[key];
        }
    });

    return filters;
};

const submitFilters = (filters: Record<string, string>) => {
    router.get(containerRoutes.index.url(), filters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const handleSearch = () => {
    submitFilters(cleanedFilters());
};

const debouncedHandleSearch = debounce(() => {
    handleSearch();
}, 400);

onBeforeUnmount(() => {
    debouncedHandleSearch.cancel();
});

const clearFilters = () => {
    debouncedHandleSearch.cancel();
    searchForm.value = normalizeFilters({});
    submitFilters({});
};

const placeholder = computed(() => t('common.placeholders.notAvailable'));

const formatWeight = (weight: number | string | null | undefined): string => {
    if (weight === null || weight === undefined) return placeholder.value;
    const numValue = typeof weight === 'string' ? parseFloat(weight) : weight;
    if (isNaN(numValue)) return placeholder.value;
    return numValue.toLocaleString();
};

const formatMoisture = (
    moisture: number | string | null | undefined,
): string => {
    if (moisture === null || moisture === undefined)
        return placeholder.value;
    const numValue =
        typeof moisture === 'string' ? parseFloat(moisture) : moisture;
    if (isNaN(numValue)) return placeholder.value;
    return `${numValue.toFixed(1)}%`;
};

const formatOuturn = (outurn: number | string | null | undefined): string => {
    if (outurn === null || outurn === undefined) return placeholder.value;
    const numValue = typeof outurn === 'string' ? parseFloat(outurn) : outurn;
    if (isNaN(numValue)) return placeholder.value;
    return t('containers.index.table.outturnValue', {
        value: numValue.toFixed(2),
    });
};

const viewContainer = (container: Container) => {
    const identifier = container.container_number || container.id;
    router.visit(containerRoutes.show.url(identifier.toString()));
};

const editContainer = (container: Container) => {
    const identifier = container.container_number || container.id;
    router.visit(containerRoutes.edit.url(identifier.toString()));
};

const openDeleteDialog = (container: Container) => {
    containerPendingDeletion.value = container;
    isConfirmOpen.value = true;
};

const closeDeleteDialog = () => {
    isConfirmOpen.value = false;
    containerPendingDeletion.value = null;
};

const confirmDelete = () => {
    if (!containerPendingDeletion.value || isDeleting.value) {
        return;
    }

    const container = containerPendingDeletion.value;
    const identifier = container.container_number || container.id;
    router.delete(containerRoutes.destroy.url(identifier.toString()), {
        preserveScroll: true,

        onStart: () => {
            isDeleting.value = true;
        },
        onSuccess: () => {
            closeDeleteDialog();
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const paginationLinks = computed(() => {
    const links = pagination.value.links ?? [];
    return links.slice(1, links.length - 1);
});

const previousLink = computed<PaginationLink>(() => {
    const links = pagination.value.links ?? [];
    return (
        links[0] ?? {
            url: null,
            label: t('containers.index.pagination.previous'),
            active: false,
        }
    );
});

const nextLink = computed<PaginationLink>(() => {
    const links = pagination.value.links ?? [];
    return (
        links[links.length - 1] ?? {
            url: null,
            label: t('containers.index.pagination.next'),
            active: false,
        }
    );
});
</script>

<template>
    <Head :title="t('containers.index.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">
                        {{ t('containers.index.title') }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ t('containers.index.description') }}
                    </p>
                </div>
                <div class="text-sm text-muted-foreground">
                    {{
                        t('containers.index.summary.total', {
                            count: pagination.total,
                        })
                    }}
                </div>
            </div>

            <Card class="gap-1 space-y-4 p-4">
                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5"
                >
                    <div class="space-y-2">
                        <Label for="container_number">
                            {{ t('containers.index.filters.containerNumber.label') }}
                        </Label>
                        <Input
                            id="container_number"
                            v-model="searchForm.container_number"
                            :placeholder="
                                t('containers.index.filters.containerNumber.placeholder')
                            "
                            @input="debouncedHandleSearch()"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="truck">
                            {{ t('containers.form.fields.truck.label') }}
                        </Label>
                        <Input
                            id="truck"
                            v-model="searchForm.truck"
                            :placeholder="
                                t('containers.index.filters.truck.placeholder')
                            "
                            @input="debouncedHandleSearch()"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="bill_info">
                            {{ t('containers.index.filters.billInfo.label') }}
                        </Label>
                        <Input
                            id="bill_info"
                            v-model="searchForm.bill_info"
                            :placeholder="
                                t('containers.index.filters.billInfo.placeholder')
                            "
                            @input="debouncedHandleSearch()"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_from">
                            {{ t('containers.index.filters.dateFrom.label') }}
                        </Label>
                        <Input
                            id="date_from"
                            v-model="searchForm.date_from"
                            type="date"
                            @change="handleSearch"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_to">
                            {{ t('containers.index.filters.dateTo.label') }}
                        </Label>
                        <Input
                            id="date_to"
                            v-model="searchForm.date_to"
                            type="date"
                            @change="handleSearch"
                        />
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button size="sm" @click="handleSearch">
                        <Search class="mr-2 h-4 w-4" />
                        {{ t('containers.index.filters.actions.search') }}
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters">
                        {{ t('containers.index.filters.actions.clear') }}
                    </Button>
                </div>
            </Card>

            <Card class="flex-1">
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        {{ t('containers.table.headers.containerNumber') }}
                                    </TableHead>
                                    <TableHead>
                                        {{ t('containers.table.headers.truck') }}
                                    </TableHead>
                                    <TableHead>
                                        {{ t('containers.index.table.headers.billInfo') }}
                                    </TableHead>
                                    <TableHead class="text-right">
                                        {{ t('containers.index.table.headers.netWeight') }}
                                    </TableHead>
                                    <TableHead class="text-right">
                                        {{ t('containers.index.table.headers.moisture') }}
                                    </TableHead>
                                    <TableHead class="text-right">
                                        {{ t('containers.index.table.headers.outturn') }}
                                    </TableHead>
                                    <TableHead>
                                        {{ t('containers.index.table.headers.created') }}
                                    </TableHead>
                                    <TableHead class="w-[120px] text-right">
                                        {{ t('containers.index.table.headers.actions') }}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="container in containers"
                                    :key="container.id"
                                    class="cursor-pointer hover:bg-muted/50"
                                    @click="viewContainer(container)"
                                >
                                    <TableCell class="font-medium">
                                        {{
                                            container.container_number ||
                                            placeholder
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        {{ container.truck || placeholder }}
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            v-if="container.bill"
                                            class="space-y-1"
                                        >
                                            <div class="font-medium">
                                                {{
                                                    container.bill.bill_number ||
                                                    t(
                                                        'containers.index.table.billNumberFallback',
                                                        { id: container.bill.id },
                                                    )
                                                }}
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    t(
                                                        'containers.index.table.billParties',
                                                        {
                                                            seller:
                                                                container.bill
                                                                    .seller ||
                                                                placeholder,
                                                            buyer:
                                                                container.bill
                                                                    .buyer ||
                                                                placeholder,
                                                        },
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="text-muted-foreground"
                                        >
                                            {{ placeholder }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right font-mono">
                                        {{ formatWeight(container.w_net) }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <span
                                            :class="
                                                container.average_moisture &&
                                                container.average_moisture > 11
                                                    ? 'font-medium text-destructive'
                                                    : ''
                                            "
                                        >
                                            {{
                                                formatMoisture(
                                                    container.average_moisture,
                                                )
                                            }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right font-mono">
                                        {{
                                            formatOuturn(container.outturn_rate)
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                new Date(
                                                    container.created_at,
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label="Edit container"
                                            @click.stop="
                                                editContainer(container)
                                            "
                                        >
                                            <Pencil class="h-4 w-4" />
                                            <span class="sr-only">
                                                {{ t('containers.index.sr.edit') }}
                                            </span>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:text-destructive"
                                            aria-label="Delete container"
                                            @click.stop="
                                                openDeleteDialog(container)
                                            "
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            <span class="sr-only">
                                                {{ t('containers.index.sr.delete') }}
                                            </span>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="containers.length === 0">
                                    <TableCell
                                        colspan="8"
                                        class="py-8 text-center text-muted-foreground"
                                    >
                                        {{ t('containers.index.empty') }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <div
                v-if="pagination.last_page > 1"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    {{
                        t('containers.index.pagination.summary', {
                            from: pagination.from,
                            to: pagination.to,
                            total: pagination.total,
                        })
                    }}
                </div>
                <Pagination>
                    <PaginationList>
                        <PaginationListItem>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!previousLink.url"
                                @click="goToPage(previousLink.url)"
                            >
                                {{ t('containers.index.pagination.previous') }}
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem
                            v-for="link in paginationLinks"
                            :key="link.label"
                        >
                            <Button
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                :disabled="!link.url"
                                @click="goToPage(link.url)"
                            >
                                <span v-html="link.label" />
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!nextLink.url"
                                @click="goToPage(nextLink.url)"
                            >
                                {{ t('containers.index.pagination.next') }}
                            </Button>
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>

        <Dialog v-model:open="isConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ t('containers.index.dialog.delete.title') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            t('containers.index.dialog.delete.description', {
                                label: pendingContainerLabel,
                            })
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button
                        variant="outline"
                        @click="closeDeleteDialog"
                        :disabled="isDeleting"
                    >
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button
                        variant="destructive"
                        @click="confirmDelete"
                        :disabled="isDeleting"
                    >
                        {{
                            isDeleting
                                ? t('common.states.deleting')
                                : t('common.actions.delete')
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
