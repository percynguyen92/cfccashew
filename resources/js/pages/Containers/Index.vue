<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pagination, PaginationList, PaginationListItem } from '@/components/ui/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import * as containerRoutes from '@/routes/containers';
import type { Container } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Pencil, Search, Trash2 } from 'lucide-vue-next';
import { debounce } from 'lodash-es';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

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

    return container.container_number || `ID ${container.id}`;
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

const formatWeight = (weight: number | string | null | undefined): string => {
    if (weight === null || weight === undefined) return '-';
    const numValue = typeof weight === 'string' ? parseFloat(weight) : weight;
    if (isNaN(numValue)) return '-';
    return numValue.toLocaleString();
};

const formatMoisture = (moisture: number | string | null | undefined): string => {
    if (moisture === null || moisture === undefined) return '-';
    const numValue = typeof moisture === 'string' ? parseFloat(moisture) : moisture;
    if (isNaN(numValue)) return '-';
    return `${numValue.toFixed(1)}%`;
};

const formatOuturn = (outurn: number | string | null | undefined): string => {
    if (outurn === null || outurn === undefined) return '-';
    const numValue = typeof outurn === 'string' ? parseFloat(outurn) : outurn;
    if (isNaN(numValue)) return '-';
    return `${numValue.toFixed(2)} lbs/80kg`;
};

const viewContainer = (container: Container) => {
    // Use container number if available, otherwise fall back to ID
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
    return links[0] ?? { url: null, label: 'Previous', active: false };
});

const nextLink = computed<PaginationLink>(() => {
    const links = pagination.value.links ?? [];
    return links[links.length - 1] ?? { url: null, label: 'Next', active: false };
});
</script>

<template>

    <Head title="Containers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Package class="h-6 w-6" />
                    <h1 class="text-2xl font-semibold">Containers</h1>
                </div>
                <div class="text-sm text-muted-foreground">
                    {{ pagination.total }} containers total
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <div class="space-y-2">
                        <Label for="container_number">Container Number</Label>
                        <Input id="container_number" v-model="searchForm.container_number"
                            placeholder="Search container number..." @input="debouncedHandleSearch()" />
                    </div>
                    <div class="space-y-2">
                        <Label for="truck">Truck</Label>
                        <Input id="truck" v-model="searchForm.truck" placeholder="Search truck..."
                            @input="debouncedHandleSearch()" />
                    </div>
                    <div class="space-y-2">
                        <Label for="bill_info">Bill Info</Label>
                        <Input id="bill_info" v-model="searchForm.bill_info" placeholder="Bill number, seller, buyer..."
                            @input="debouncedHandleSearch()" />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_from">From Date</Label>
                        <Input id="date_from" v-model="searchForm.date_from" type="date" @change="handleSearch" />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_to">To Date</Label>
                        <Input id="date_to" v-model="searchForm.date_to" type="date" @change="handleSearch" />
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <Button @click="handleSearch" size="sm">
                        <Search class="mr-2 h-4 w-4" />
                        Search
                    </Button>
                    <Button @click="clearFilters" variant="outline" size="sm">
                        Clear Filters
                    </Button>
                </div>
            </div>

            <!-- Containers Table -->
            <div class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Container Number</TableHead>
                            <TableHead>Truck</TableHead>
                            <TableHead>Bill Info</TableHead>
                            <TableHead class="text-right">Net Weight</TableHead>
                            <TableHead class="text-right">Moisture</TableHead>
                            <TableHead class="text-right">Outurn</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="w-[100px]">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="container in containers" :key="container.id"
                            class="cursor-pointer hover:bg-muted/50" @click="viewContainer(container)">
                            <TableCell class="font-medium">
                                {{ container.container_number || '-' }}
                            </TableCell>
                            <TableCell>
                                {{ container.truck || '-' }}
                            </TableCell>
                            <TableCell>
                                <div v-if="container.bill" class="space-y-1">
                                    <div class="font-medium">
                                        {{ container.bill.bill_number || `Bill ${container.bill.id}` }}
                                    </div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ container.bill.seller }} / {{ container.bill.buyer }}
                                    </div>
                                </div>
                                <div v-else class="text-muted-foreground">-</div>
                            </TableCell>
                            <TableCell class="text-right">
                                {{ formatWeight(container.w_net) }}
                            </TableCell>
                            <TableCell class="text-right">
                                <span :class="container.average_moisture && container.average_moisture > 11
                                    ? 'text-red-600 font-medium'
                                    : ''">
                                    {{ formatMoisture(container.average_moisture) }}
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                {{ formatOuturn(container.outturn_rate) }}
                            </TableCell>
                            <TableCell>
                                <div class="text-sm text-muted-foreground">
                                    {{ new Date(container.created_at).toLocaleDateString() }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Button variant="ghost" size="icon" aria-label="Edit container"
                                    @click.stop="editContainer(container)">
                                    <Pencil class="h-4 w-4" />
                                    <span class="sr-only">Edit container</span>
                                </Button>
                                <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive"
                                    aria-label="Delete container" @click.stop="openDeleteDialog(container)">
                                    <Trash2 class="h-4 w-4" />
                                    <span class="sr-only">Delete container</span>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="containers.length === 0">
                            <TableCell colspan="8" class="py-8 text-center text-muted-foreground">
                                No containers found. Try adjusting your search filters.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} containers
                </div>
                <Pagination>
                    <PaginationList>
                        <PaginationListItem>
                            <Button variant="outline" size="sm" :disabled="!previousLink.url"
                                @click="goToPage(previousLink.url)">
                                Previous
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem v-for="link in paginationLinks" :key="link.label">
                            <Button :variant="link.active ? 'default' : 'outline'" size="sm" :disabled="!link.url"
                                @click="goToPage(link.url)">
                                <span v-html="link.label" />
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem>
                            <Button variant="outline" size="sm" :disabled="!nextLink.url"
                                @click="goToPage(nextLink.url)">
                                Next
                            </Button>
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>
        <!-- Confirm Delete Dialog -->
        <Dialog v-model:open="isConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete container</DialogTitle>
                    <DialogDescription>
                        This action permanently removes container {{ pendingContainerLabel }} and all related data.
                        This cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="closeDeleteDialog" :disabled="isDeleting">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete" :disabled="isDeleting">
                        {{ isDeleting ? 'Deleting...' : 'Delete' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
