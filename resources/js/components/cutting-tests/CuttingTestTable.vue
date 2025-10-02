<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import type { CuttingTest } from '@/types';
import { Loader2, Trash2, AlertTriangle, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CuttingTestAlerts from './CuttingTestAlerts.vue';
import { useCuttingTestValidation } from '@/composables/useCuttingTestValidation';

interface Props {
    tests: CuttingTest[];
    deletingId?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
    tests: () => [],
    deletingId: null,
});

const emit = defineEmits<{
    edit: [CuttingTest];
    delete: [CuttingTest];
}>();

const rows = computed(() =>
    (props.tests ?? []).filter((test): test is CuttingTest => Boolean(test)),
);

const { t } = useI18n();

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

const formatMoisture = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value, 1);
    return rendered === '-'
        ? t('common.placeholders.notAvailable')
        : t('cuttingTests.table.moistureValue', { value: rendered });
};

const formatWeight = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value);
    return rendered === '-'
        ? t('common.placeholders.notAvailable')
        : t('cuttingTests.table.weightValue', { value: rendered });
};

const formatOutturn = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value, 2);
    return rendered === '-'
        ? t('common.placeholders.notAvailable')
        : t('cuttingTests.table.outturnValue', { value: rendered });
};

// Check if any cutting test has validation alerts
const hasAnyAlerts = computed(() => {
    return rows.value.some(test => {
        const validation = useCuttingTestValidation(computed(() => test));
        return validation.hasAlerts.value;
    });
});
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>
                    {{ t('cuttingTests.table.headers.moisture') }}
                </TableHead>
                <TableHead>
                    {{ t('cuttingTests.table.headers.rejectNut') }}
                </TableHead>
                <TableHead>
                    {{ t('cuttingTests.table.headers.defectNut') }}
                </TableHead>
                <TableHead>
                    {{ t('cuttingTests.table.headers.goodKernel') }}
                </TableHead>
                <TableHead>
                    {{ t('cuttingTests.table.headers.outturn') }}
                </TableHead>
                <TableHead v-if="hasAnyAlerts">
                    {{ t('cuttingTests.table.headers.alerts') }}
                </TableHead>
                <TableHead class="w-32 text-right">
                    {{ t('cuttingTests.table.headers.actions') }}
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="test in rows" :key="test.id">
                <TableCell>
                    <div class="flex items-center gap-2">
                        <span :class="test.moisture && test.moisture > 11 ? 'text-red-600 font-medium' : ''">
                            {{ formatMoisture(test.moisture) }}
                        </span>
                        <div v-if="test.moisture && test.moisture > 11" class="flex items-center">
                            <Badge variant="destructive" class="text-xs">
                                <AlertTriangle class="h-3 w-3 mr-1" />
                                {{ t('cuttingTests.table.highMoisture') }}
                            </Badge>
                        </div>
                    </div>
                </TableCell>
                <TableCell>{{ formatWeight(test.w_reject_nut) }}</TableCell>
                <TableCell>{{ formatWeight(test.w_defective_nut) }}</TableCell>
                <TableCell>{{ formatWeight(test.w_good_kernel) }}</TableCell>
                <TableCell>{{ formatOutturn(test.outturn_rate) }}</TableCell>
                <TableCell v-if="hasAnyAlerts" class="max-w-xs">
                    <CuttingTestAlerts :cutting-test="test" :max-alerts="3" />
                </TableCell>
                <TableCell>
                    <div class="flex justify-end gap-2">
                        <Button v-if="test.id" size="sm" variant="ghost" @click="emit('edit', test)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button v-if="test.id" size="sm" variant="ghost" class="text-destructive hover:text-destructive"
                            :disabled="props.deletingId === test.id" @click="emit('delete', test)">
                            <Loader2 v-if="props.deletingId === test.id" class="h-4 w-4 animate-spin" />
                            <Trash2 v-else class="h-4 w-4" />
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
