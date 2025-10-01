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
import type { Container } from '@/types';
import { Calculator, Loader2, Trash2, AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';
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

const emit = defineEmits<{
    (event: 'edit', container: Container): void;
    (event: 'delete', container: Container): void;
}>();

const rows = computed(() =>
    (props.containers ?? []).filter((container): container is Container =>
        Boolean(container),
    ),
);

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

// Get condition indicator color
const getConditionColor = (condition: string | null): string => {
    if (!condition) return 'bg-gray-400';

    const lowerCondition = condition.toLowerCase();
    if (lowerCondition.includes('good') || lowerCondition.includes('excellent')) {
        return 'bg-green-500';
    } else if (lowerCondition.includes('fair') || lowerCondition.includes('average')) {
        return 'bg-yellow-500';
    } else if (lowerCondition.includes('poor') || lowerCondition.includes('damaged')) {
        return 'bg-red-500';
    }
    return 'bg-blue-500';
};

// Check for weight discrepancies
const hasWeightDiscrepancy = (container: Container): boolean => {
    if (!container.w_net || !container.w_gross) return false;

    // Check if net weight seems reasonable compared to gross
    const ratio = container.w_net / container.w_gross;
    return ratio < 0.7 || ratio > 0.95; // Flag if net is less than 70% or more than 95% of gross
};

// Get weight discrepancy details
const getWeightDiscrepancyDetails = (container: Container): { type: 'warning' | 'error'; message: string } | null => {
    if (!container.w_net || !container.w_gross) return null;

    const ratio = container.w_net / container.w_gross;

    if (ratio < 0.5) {
        return {
            type: 'error',
            message: 'Net weight is less than 50% of gross weight'
        };
    } else if (ratio < 0.7) {
        return {
            type: 'warning',
            message: 'Net weight seems low compared to gross weight'
        };
    } else if (ratio > 0.95) {
        return {
            type: 'warning',
            message: 'Net weight is very close to gross weight'
        };
    }

    return null;
};

// Check if calculated weights match stored weights (for manual vs calculated indicator)
const isWeightCalculated = (container: Container): { gross: boolean; tare: boolean; net: boolean } => {
    // This would ideally check if weights were calculated vs manually entered
    // For now, we assume weights are calculated if all required fields are present
    const hasRequiredFields = !!(container.w_total && container.w_truck && container.w_container &&
        container.quantity_of_bags && container.w_jute_bag);

    return {
        gross: hasRequiredFields && container.w_gross !== null,
        tare: hasRequiredFields && container.w_tare !== null,
        net: hasRequiredFields && container.w_net !== null,
    };
};
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>
                    {{ t('containers.table.headers.containerNumber') }}
                </TableHead>
                <TableHead>{{ t('containers.table.headers.truck') }}</TableHead>
                <TableHead>
                    {{ t('containers.table.headers.quantityOfBags') }}
                </TableHead>
                <TableHead>{{ t('containers.table.headers.condition') }}</TableHead>
                <TableHead>{{ t('containers.table.headers.net') }}</TableHead>
                <TableHead>{{
                    t('containers.table.headers.moisture')
                }}</TableHead>
                <TableHead>{{
                    t('containers.table.headers.outturn')
                }}</TableHead>
                <TableHead class="w-32 text-right">
                    {{ t('containers.table.headers.actions') }}
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="container in rows" :key="container.id">
                <TableCell class="font-medium">
                    {{
                        container.container_number ||
                        t('common.placeholders.notAvailable')
                    }}
                </TableCell>
                <TableCell>
                    {{
                        container.truck || t('common.placeholders.notAvailable')
                    }}
                </TableCell>
                <TableCell>
                    {{
                        formatNumber(container.quantity_of_bags) === '-'
                            ? t('common.placeholders.notAvailable')
                            : formatNumber(container.quantity_of_bags)
                    }}
                </TableCell>
                <TableCell>
                    <div class="flex items-center gap-2">
                        <span :class="getConditionColor(container.container_condition)"
                            class="w-2 h-2 rounded-full"></span>
                        <span class="text-xs">{{ container.container_condition || '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span :class="getConditionColor(container.seal_condition)" class="w-2 h-2 rounded-full"></span>
                        <span class="text-xs text-muted-foreground">{{ container.seal_condition || '-' }}</span>
                    </div>
                </TableCell>
                <TableCell>
                    <div class="flex items-center gap-2">
                        <span :class="hasWeightDiscrepancy(container) ? 'text-red-600 font-medium' : ''">
                            {{
                                formatWeight(container.w_net) === '-'
                                    ? t('common.placeholders.notAvailable')
                                    : formatWeight(container.w_net)
                            }}
                        </span>
                        <!-- Weight calculation indicator -->
                        <div v-if="container.w_net !== null" class="flex items-center">
                            <Calculator v-if="isWeightCalculated(container).net" class="h-3 w-3 text-green-600"
                                :title="'Calculated weight'" />
                            <span v-else class="h-3 w-3 rounded-full bg-blue-500" :title="'Manual weight'" />
                        </div>
                    </div>
                    <!-- Weight discrepancy warning -->
                    <div v-if="getWeightDiscrepancyDetails(container)" class="text-xs mt-1"
                        :class="getWeightDiscrepancyDetails(container)?.type === 'error' ? 'text-red-600' : 'text-yellow-600'">
                        ⚠️ {{ getWeightDiscrepancyDetails(container)?.message }}
                    </div>
                </TableCell>
                <TableCell>
                    <div class="flex items-center gap-2">
                        <span
                            :class="container.average_moisture && container.average_moisture > 11 ? 'text-red-600 font-medium' : ''">
                            {{
                                formatMoisture(container.average_moisture) === '-'
                                    ? t('common.placeholders.notAvailable')
                                    : formatMoisture(container.average_moisture)
                            }}
                        </span>
                        <div v-if="container.average_moisture && container.average_moisture > 11"
                            class="flex items-center">
                            <Badge variant="destructive" class="text-xs">
                                <AlertTriangle class="h-3 w-3 mr-1" />
                                High
                            </Badge>
                        </div>
                    </div>
                </TableCell>
                <TableCell>
                    {{
                        formatOutturn(container.outturn_rate) === '-'
                            ? t('common.placeholders.notAvailable')
                            : formatOutturn(container.outturn_rate)
                    }}
                </TableCell>
                <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                        <Button v-if="container.id" size="sm" variant="ghost" @click="emit('edit', container)">
                            {{ t('common.actions.edit') }}
                        </Button>
                        <Button v-if="container.id" size="sm" variant="ghost"
                            class="text-destructive hover:text-destructive"
                            :disabled="props.deletingId === container.id" @click="emit('delete', container)">
                            <Loader2 v-if="props.deletingId === container.id" class="mr-1 h-4 w-4 animate-spin" />
                            <Trash2 v-else class="mr-1 h-4 w-4" />
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
