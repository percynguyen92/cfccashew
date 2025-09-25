<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
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
import { useFiltering } from '@/composables/useFiltering';
import {
    usePagination,
    type PaginationData,
} from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import * as billRoutes from '@/routes/bills';
import type { Bill as BillModel } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Pencil,
    Plus,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type BillListItem = BillModel & {
    containers_count: number;
    final_samples_count: number;
    average_outurn: number | null;
};

interface Props {
    bills: PaginationData & { data: BillListItem[] };
    filters: {
        search: string;
        sort_by: string;
        sort_direction: 'asc' | 'desc';
    };
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();
const { goToPage, getPaginationInfo } = usePagination();
const { filters, isLoading, sortBy } = useFiltering(props.filters);

const paginationInfo = getPaginationInfo(props.bills);

const isConfirmOpen = ref(false);
const isDeleting = ref(false);
const billPendingDeletion = ref<BillListItem | null>(null);

const pendingBillLabel = computed(() => {
    const bill = billPendingDeletion.value;

    if (!bill) return '';

    return bill.bill_number || `ID ${bill.id}`;
});

const isBillFormOpen = ref(false);
const billFormMode = ref<'create' | 'edit'>('create');
const billBeingEdited = ref<BillListItem | null>(null);
const isEditingBill = computed(() => billFormMode.value === 'edit');

const getSortIcon = (column: string) => {
    if (filters.value.sort_by !== column) return ArrowUpDown;
    return filters.value.sort_direction === 'asc' ? ArrowUp : ArrowDown;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
    });
};

const handleSearch = (event: Event) => {
    const target = event.target as HTMLInputElement;
    filters.value.search = target.value;
};

const openDeleteDialog = (bill: BillListItem) => {
    billPendingDeletion.value = bill;
    isConfirmOpen.value = true;
};

const closeDeleteDialog = () => {
    isConfirmOpen.value = false;
    billPendingDeletion.value = null;
};

const confirmDelete = () => {
    if (!billPendingDeletion.value || isDeleting.value) {
        return;
    }

    const identifier =
        billPendingDeletion.value.slug || billPendingDeletion.value.id;

    router.delete(billRoutes.destroy.url(identifier), {
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

const openCreateBillDialog = () => {
    billFormMode.value = 'create';
    billBeingEdited.value = null;
    isBillFormOpen.value = true;
};

const openEditBillDialog = (bill: BillListItem) => {
    billFormMode.value = 'edit';
    billBeingEdited.value = bill;
    isBillFormOpen.value = true;
};

const closeBillFormDialog = () => {
    isBillFormOpen.value = false;
};

const handleBillFormCancel = () => {
    closeBillFormDialog();
};

const handleBillFormSuccess = () => {
    closeBillFormDialog();
};

watch(isBillFormOpen, (isOpen) => {
    if (!isOpen) {
        billFormMode.value = 'create';
        billBeingEdited.value = null;
    }
});
</script>

<template>
    <Head title="Bills" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Bills</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage Bills of Lading and their associated containers
                    </p>
                </div>
                <Button @click="openCreateBillDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Create New Bill
                </Button>
            </div>

            <!-- Search and Filters -->
            <Card class="p-4">
                <div class="flex items-center gap-4">
                    <div class="relative max-w-sm flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            :model-value="filters.search"
                            @input="handleSearch"
                            placeholder="Search by bill number, seller, or buyer..."
                            class="pl-9"
                        />
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ paginationInfo.total }} total bills
                    </div>
                </div>
            </Card>

            <!-- Bills Table -->
            <Card class="flex-1">
                <div class="relative">
                    <div
                        v-if="isLoading"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-background/50"
                    >
                        <div
                            class="h-8 w-8 animate-spin rounded-full border-b-2 border-primary"
                        ></div>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="sortBy('bill_number')"
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                    >
                                        Bill #
                                        <component
                                            :is="getSortIcon('bill_number')"
                                            class="ml-2 h-4 w-4"
                                        />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="sortBy('seller')"
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                    >
                                        Seller
                                        <component
                                            :is="getSortIcon('seller')"
                                            class="ml-2 h-4 w-4"
                                        />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="sortBy('buyer')"
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                    >
                                        Buyer
                                        <component
                                            :is="getSortIcon('buyer')"
                                            class="ml-2 h-4 w-4"
                                        />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="sortBy('containers_count')"
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                    >
                                        Containers
                                        <component
                                            :is="
                                                getSortIcon('containers_count')
                                            "
                                            class="ml-2 h-4 w-4"
                                        />
                                    </Button>
                                </TableHead>
                                <TableHead>Final Samples</TableHead>
                                <TableHead>Avg. Outurn</TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="sortBy('created_at')"
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                    >
                                        Created
                                        <component
                                            :is="getSortIcon('created_at')"
                                            class="ml-2 h-4 w-4"
                                        />
                                    </Button>
                                </TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="bill in props.bills.data"
                                :key="bill.id"
                                class="cursor-pointer"
                                @click="
                                    router.visit(
                                        billRoutes.show.url(
                                            bill.slug || bill.id,
                                        ),
                                    )
                                "
                            >
                                <TableCell class="font-medium">
                                    {{ bill.bill_number || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    {{ bill.seller || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    {{ bill.buyer || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ bill.containers_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            bill.final_samples_count >= 3
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{ bill.final_samples_count }}/3
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span
                                        v-if="bill.average_outurn"
                                        class="font-medium"
                                    >
                                        {{ bill.average_outurn.toFixed(2) }}
                                        lbs/80kg
                                    </span>
                                    <span v-else class="text-muted-foreground"
                                        >N/A</span
                                    >
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ formatDate(bill.created_at) }}
                                </TableCell>
                                <TableCell
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label="Edit bill"
                                        @click.stop="openEditBillDialog(bill)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        <span class="sr-only">Edit bill</span>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        aria-label="Delete bill"
                                        @click.stop="openDeleteDialog(bill)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span class="sr-only">Delete bill</span>
                                    </Button>
                                </TableCell>
                            </TableRow>

                            <!-- Empty State -->
                            <TableRow v-if="props.bills.data.length === 0">
                                <TableCell
                                    :colspan="8"
                                    class="py-8 text-center"
                                >
                                    <div class="text-muted-foreground">
                                        <p class="text-lg font-medium">
                                            No bills found
                                        </p>
                                        <p class="text-sm">
                                            <template v-if="filters.search">
                                                Try adjusting your search
                                                criteria
                                            </template>
                                            <template v-else>
                                                Get started by creating your
                                                first bill
                                            </template>
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </Card>

            <!-- Pagination -->
            <div
                v-if="props.bills.data.length > 0"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Showing {{ paginationInfo.from }} to
                    {{ paginationInfo.to }} of
                    {{ paginationInfo.total }} results
                </div>

                <Pagination>
                    <PaginationList>
                        <PaginationListItem>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!paginationInfo.hasPrevPage"
                                @click="goToPage(props.bills.prev_page_url)"
                            >
                                Previous
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem
                            v-for="link in props.bills.links.slice(1, -1)"
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
                                :disabled="!paginationInfo.hasNextPage"
                                @click="goToPage(props.bills.next_page_url)"
                            >
                                Next
                            </Button>
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>

        <Dialog v-model:open="isBillFormOpen">
            <DialogContent class="max-w-3xl">
                <BillForm
                    v-if="isBillFormOpen"
                    :bill="billBeingEdited || undefined"
                    :is-editing="isEditingBill"
                    @success="handleBillFormSuccess"
                    @cancel="handleBillFormCancel"
                />
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete bill</DialogTitle>
                    <DialogDescription>
                        This action permanently removes bill
                        {{ pendingBillLabel }} and all related data. This cannot
                        be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button
                        variant="outline"
                        @click="closeDeleteDialog"
                        :disabled="isDeleting"
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        @click="confirmDelete"
                        :disabled="isDeleting"
                    >
                        {{ isDeleting ? 'Deleting...' : 'Delete' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
