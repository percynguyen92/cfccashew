<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Pagination,
    PaginationList,
    PaginationListItem,
} from '@/components/ui/pagination';
import type { Container } from '@/types';
import { Loader2, Trash2, AlertTriangle, ChevronLeft, ChevronRight, Pencil } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    containers: Container[];
    deletingId?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
    containers: () => [],
    deletingId: null,
});

const { t } = useI18n();
const currentPage = ref(1);
const itemsPerPage = 10;

const emit = defineEmits<{
    (event: 'edit', container: Container): void;
    (event: 'delete', container: Container): void;
}>();

const allContainers = computed(() =>
    (props.containers ?? []).filter((container): container is Container =>
        Boolean(container),
    ),
);

const totalPages = computed(() =>
    Math.ceil(allContainers.value.length / itemsPerPage)
);

const rows = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return allContainers.value.slice(start, end);
});

const paginationInfo = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage + 1;
    const end = Math.min(currentPage.value * itemsPerPage, allContainers.value.length);
    return {
        start,
        end,
        total: allContainers.value.length,
        hasNext: currentPage.value < totalPages.value,
        hasPrev: currentPage.value > 1,
    };
});

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const nextPage = () => {
    if (paginationInfo.value.hasNext) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (paginationInfo.value.hasPrev) {
        currentPage.value--;
    }
};

const formatNumber = (
    value: number | string | null | undefined,
    fractionDigits = 0,
): string => {
    if (value === null || value === undefined) {
        return '-';
    }

    const numeric =
        typeof value === 'number' ? value : Number.parseFloat(value as string);

    if (Number.isNaN(numeric)) {
        return '-';
    }

    return numeric.toLocaleString(undefined, {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits,
    });
};

const formatWeight = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value, 0);
    return rendered === '-' ? rendered : `${rendered} kg`;
};

const formatMoisture = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value, 1);
    return rendered === '-' ? rendered : `${rendered}%`;
};

const formatOutturn = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value, 2);
    return rendered === '-' ? rendered : `${rendered} lbs/80kg`;
};



// Check for weight discrepancies
const hasWeightDiscrepancy = (container: Container): boolean => {
    if (!container.w_net || !container.w_gross) return false;

    // Check if net weight seems reasonable compared to gross
    const ratio = container.w_net / container.w_gross;
    return ratio < 0.7; // Flag if net is less than 70% or more than 95% of gross
};

// Get weight discrepancy details
const getWeightDiscrepancyDetails = (container: Container): { type: 'warning' | 'error' } | null => {
    if (!container.w_net || !container.w_gross) return null;

    const ratio = container.w_net / container.w_gross;

    if (ratio < 0.5) {
        return {
            type: 'error'
        };
    } else if (ratio < 0.7) {
        return {
            type: 'warning'
        };
    }

    return null;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Container count and pagination info -->
        <div v-if="allContainers.length > 0" class="flex items-center justify-between text-sm text-muted-foreground">
            <div>
                {{ t('containers.table.pagination.showing', {
                    start: paginationInfo.start,
                    end: paginationInfo.end,
                    total: paginationInfo.total
                }) }}
            </div>
            <div v-if="totalPages > 1">
                {{ t('containers.table.pagination.page', { current: currentPage, total: totalPages }) }}
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="min-w-[120px]">{{ t('containers.table.headers.containerNumber') }}</TableHead>
                        <TableHead class="min-w-[100px]">{{ t('containers.table.headers.truck') }}</TableHead>
                        <TableHead class="min-w-[80px]">{{ t('containers.table.headers.bags') }}</TableHead>
                        <TableHead class="min-w-[100px]">{{ t('containers.table.headers.totalWeight') }}</TableHead>
                        <TableHead class="min-w-[100px]">{{ t('containers.table.headers.truckWeight') }}</TableHead>
                        <TableHead class="min-w-[120px]">{{ t('containers.table.headers.containerWeight') }}</TableHead>
                        <TableHead class="min-w-[100px]">{{ t('containers.table.headers.grossWeight') }}</TableHead>
                        <TableHead class="min-w-[100px]">{{ t('containers.table.headers.tareWeight') }}</TableHead>
                        <TableHead class="min-w-[100px]">{{ t('containers.table.headers.netWeight') }}</TableHead>
                        <TableHead class="min-w-[80px]">{{ t('containers.table.headers.moisture') }}</TableHead>
                        <TableHead class="min-w-[100px]">{{ t('containers.table.headers.outturnRate') }}</TableHead>
                        <TableHead class="w-32 text-right">{{ t('containers.table.headers.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="container in rows" :key="container.id">
                        <TableCell class="font-medium">
                            {{ container.container_number || '-' }}
                        </TableCell>
                        <TableCell>
                            {{ container.truck || '-' }}
                        </TableCell>
                        <TableCell>
                            {{ formatNumber(container.quantity_of_bags) }}
                        </TableCell>
                        <TableCell>
                            {{ formatWeight(container.w_total) }}
                        </TableCell>
                        <TableCell>
                            {{ formatWeight(container.w_truck) }}
                        </TableCell>
                        <TableCell>
                            {{ formatWeight(container.w_container) }}
                        </TableCell>
                        <TableCell>
                            {{ formatWeight(container.w_gross) }}
                        </TableCell>
                        <TableCell>
                            {{ formatWeight(container.w_tare) }}
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <span :class="hasWeightDiscrepancy(container) ? 'text-red-600 font-medium' : ''">
                                    {{ formatWeight(container.w_net) }}
                                </span>
                            </div>
                            <!-- Weight discrepancy warning -->
                            <div v-if="getWeightDiscrepancyDetails(container)" class="text-xs mt-1"
                                :class="getWeightDiscrepancyDetails(container)?.type === 'error' ? 'text-red-600' : 'text-yellow-600'">
                                ⚠️ {{
                                    t(`containers.table.weightDiscrepancy.${getWeightDiscrepancyDetails(container)?.type}`)
                                }}
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <span
                                    :class="container.average_moisture && container.average_moisture > 11 ? 'text-red-600 font-medium' : ''">
                                    {{ formatMoisture(container.average_moisture) }}
                                </span>
                                <div v-if="container.average_moisture && container.average_moisture > 11"
                                    class="flex items-center">
                                    <Badge variant="destructive" class="text-xs">
                                        <AlertTriangle class="h-3 w-3 mr-1" />
                                        {{ t('containers.table.highMoisture') }}
                                    </Badge>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            {{ formatOutturn(container.outturn_rate) }}
                        </TableCell>
                        <TableCell>
                            <div class="flex justify-end gap-2">
                                <Button v-if="container.id" size="sm" variant="ghost" @click="emit('edit', container)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button v-if="container.id" size="sm" variant="ghost"
                                    class="text-destructive hover:text-destructive"
                                    :disabled="props.deletingId === container.id" @click="emit('delete', container)">
                                    <Loader2 v-if="props.deletingId === container.id" class="h-4 w-4 animate-spin" />
                                    <Trash2 v-else class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <!-- Empty state -->
                    <TableRow v-if="allContainers.length === 0">
                        <TableCell :colspan="12" class="py-8 text-center text-muted-foreground">
                            {{ t('containers.table.empty') }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-center">
            <Pagination>
                <PaginationList>
                    <PaginationListItem>
                        <Button variant="outline" size="sm" :disabled="!paginationInfo.hasPrev" @click="prevPage">
                            <ChevronLeft class="h-4 w-4" />
                            {{ t('containers.table.pagination.previous') }}
                        </Button>
                    </PaginationListItem>

                    <PaginationListItem v-for="page in totalPages" :key="page">
                        <Button :variant="page === currentPage ? 'default' : 'outline'" size="sm"
                            @click="goToPage(page)">
                            {{ page }}
                        </Button>
                    </PaginationListItem>

                    <PaginationListItem>
                        <Button variant="outline" size="sm" :disabled="!paginationInfo.hasNext" @click="nextPage">
                            {{ t('containers.table.pagination.next') }}
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </PaginationListItem>
                </PaginationList>
            </Pagination>
        </div>
    </div>
</template>
