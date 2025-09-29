<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import { Badge } from '@/components/ui/badge';
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
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Pencil,
    Plus,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

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

const { t } = useI18n();
const { breadcrumbs } = useBreadcrumbs();
const { goToPage, getPaginationInfo } = usePagination();
const { filters, isLoading, sortBy } = useFiltering(props.filters);
const page = usePage();

const paginationInfo = getPaginationInfo(props.bills);
const tableRows = ref<BillListItem[]>([...props.bills.data]);
const highlightedBillId = ref<number | null>(null);
let highlightTimer: number | null = null;
const isBrowser = typeof window !== 'undefined';

const clearHighlightTimer = () => {
    if (highlightTimer && isBrowser) {
        window.clearTimeout(highlightTimer);
        highlightTimer = null;
    }
};

const updateHighlightedRow = (billId: number | null) => {
    highlightedBillId.value = billId;

    clearHighlightTimer();

    if (billId !== null && isBrowser) {
        highlightTimer = window.setTimeout(() => {
            if (highlightedBillId.value === billId) {
                highlightedBillId.value = null;
            }
        }, 2500);
    }
};

const upsertTableRow = (bill: BillListItem) => {
    const existingIndex = tableRows.value.findIndex(
        (item) => item.id === bill.id,
    );

    if (existingIndex >= 0) {
        tableRows.value.splice(existingIndex, 1, bill);
    } else {
        tableRows.value = [bill, ...tableRows.value];
    }

    updateHighlightedRow(bill.id);
};

watch(
    () => props.bills.data,
    (newData) => {
        tableRows.value = [...newData];
        if (highlightedBillId.value !== null) {
            const stillExists = tableRows.value.some(
                (item) => item.id === highlightedBillId.value,
            );

            if (!stillExists) {
                updateHighlightedRow(null);
            }
        }
    },
);

watch(
    () => (page.props.flash as { createdBill?: BillListItem } | undefined)?.createdBill,
    (createdBill) => {
        if (!createdBill) {
            return;
        }

        upsertTableRow(createdBill);
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    clearHighlightTimer();
});

const isConfirmOpen = ref(false);
const isDeleting = ref(false);
const billPendingDeletion = ref<BillListItem | null>(null);

const pendingBillLabel = computed(() => {
    const bill = billPendingDeletion.value;

    if (!bill) return '';

    return (
        bill.bill_number ||
        t('bills.index.dialog.delete.fallback', { id: bill.id })
    );
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

const currentIndexUrl = computed(() => page.url);

watch(isBillFormOpen, (isOpen) => {
    if (!isOpen) {
        billFormMode.value = 'create';
        billBeingEdited.value = null;
    }
});
</script>

<template>
    <Head :title="t('bills.index.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">
                        {{ t('bills.index.title') }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ t('bills.index.description') }}
                    </p>
                </div>
                <Button @click="openCreateBillDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('bills.index.actions.create') }}
                </Button>
            </div>

            <!-- Search and Filters -->
            <Card class="gap-1 py-4">
                <CardContent class="flex flex-wrap items-center gap-4 px-4">
                    <div class="relative max-w-sm flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            :model-value="filters.search"
                            @input="handleSearch"
                            :placeholder="t('bills.index.search.placeholder')"
                            class="pl-9"
                        />
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{
                            t('bills.index.search.total', {
                                count: paginationInfo.total,
                            })
                        }}
                    </div>
                </CardContent>
            </Card>

            <!-- Bills Table -->
            <Card class="flex-1">
                <CardContent class="relative">
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
                                        {{
                                            t(
                                                'bills.index.table.headers.billNumber',
                                            )
                                        }}
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
                                        {{
                                            t('bills.index.table.headers.seller')
                                        }}
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
                                        {{ t('bills.index.table.headers.buyer') }}
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
                                        {{
                                            t(
                                                'bills.index.table.headers.containers',
                                            )
                                        }}
                                        <component
                                            :is="
                                                getSortIcon('containers_count')
                                            "
                                            class="ml-2 h-4 w-4"
                                        />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    {{
                                        t(
                                            'bills.index.table.headers.finalSamples',
                                        )
                                    }}
                                </TableHead>
                                <TableHead>
                                    {{
                                        t(
                                            'bills.index.table.headers.averageOutturn',
                                        )
                                    }}
                                </TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="sortBy('created_at')"
                                        class="h-auto p-0 font-medium hover:bg-transparent"
                                    >
                                        {{
                                            t(
                                                'bills.index.table.headers.created',
                                            )
                                        }}
                                        <component
                                            :is="getSortIcon('created_at')"
                                            class="ml-2 h-4 w-4"
                                        />
                                    </Button>
                                </TableHead>
                                <TableHead class="text-right">
                                    {{ t('bills.index.table.headers.actions') }}
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="bill in tableRows"
                                :key="bill.id"
                                :class="[
                                    'cursor-pointer transition-colors duration-500 ease-out',
                                    highlightedBillId === bill.id
                                        ? 'highlighted-row'
                                        : '',
                                ]"
                                @click="
                                    router.visit(
                                        billRoutes.show.url(
                                            bill.slug || bill.id,
                                        ),
                                    )
                                "
                            >
                                <TableCell class="font-medium">
                                    {{
                                        bill.bill_number ||
                                        t('common.placeholders.notAvailable')
                                    }}
                                </TableCell>
                                <TableCell>
                                    {{
                                        bill.seller ||
                                        t('common.placeholders.notAvailable')
                                    }}
                                </TableCell>
                                <TableCell>
                                    {{
                                        bill.buyer ||
                                        t('common.placeholders.notAvailable')
                                    }}
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
                                        {{
                                            t('bills.index.table.finalSamples', {
                                                count: bill.final_samples_count,
                                            })
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span
                                        v-if="bill.average_outurn"
                                        class="font-medium"
                                    >
                                        {{
                                            t(
                                                'bills.index.table.averageOutturnValue',
                                                {
                                                    value: bill.average_outurn.toFixed(
                                                        2,
                                                    ),
                                                },
                                            )
                                        }}
                                    </span>
                                    <span v-else class="text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'common.placeholders.notAvailable',
                                                )
                                            }}
                                        </span
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
                                        :aria-label="t('bills.index.sr.edit')"
                                        @click.stop="openEditBillDialog(bill)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        <span class="sr-only">
                                            {{ t('bills.index.sr.edit') }}
                                        </span>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        :aria-label="t('bills.index.sr.delete')"
                                        @click.stop="openDeleteDialog(bill)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span class="sr-only">
                                            {{ t('bills.index.sr.delete') }}
                                        </span>
                                    </Button>
                                </TableCell>
                            </TableRow>

                            <!-- Empty State -->
                            <TableRow v-if="tableRows.length === 0">
                                <TableCell
                                    :colspan="8"
                                    class="py-8 text-center"
                                >
                                    <div class="text-muted-foreground">
                                        <p class="text-lg font-medium">
                                            {{ t('bills.index.empty.title') }}
                                        </p>
                                        <p class="text-sm">
                                            <template v-if="filters.search">
                                                {{
                                                    t(
                                                        'bills.index.empty.search',
                                                    )
                                                }}
                                            </template>
                                            <template v-else>
                                                {{
                                                    t(
                                                        'bills.index.empty.default',
                                                    )
                                                }}
                                            </template>
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div
                v-if="tableRows.length > 0"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    {{
                        t('bills.index.pagination.summary', {
                            from: paginationInfo.from,
                            to: paginationInfo.to,
                            total: paginationInfo.total,
                        })
                    }}
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
                                {{ t('bills.index.pagination.previous') }}
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
                                {{ t('bills.index.pagination.next') }}
                            </Button>
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>

        <Dialog v-model:open="isBillFormOpen">
            <!-- Bill Create/Edit Form -->
            <DialogContent
                class="max-h-[90vh] w-full overflow-y-auto sm:max-w-4xl lg:max-w-5xl"
            >
                <BillForm
                    v-if="isBillFormOpen"
                    :bill="billBeingEdited || undefined"
                    :is-editing="isEditingBill"
                    :redirect-url="currentIndexUrl"
                    @success="handleBillFormSuccess"
                    @cancel="handleBillFormCancel"
                />
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ t('bills.index.dialog.delete.title') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            t('bills.index.dialog.delete.description', {
                                label: pendingBillLabel,
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

<style scoped>
.highlighted-row {
    animation: bill-row-highlight 2.4s ease-in-out forwards;
}

@keyframes bill-row-highlight {
    0% {
        background-color: rgba(34, 197, 94, 0.25);
    }

    60% {
        background-color: rgba(34, 197, 94, 0.12);
    }

    100% {
        background-color: transparent;
    }
}
</style>
