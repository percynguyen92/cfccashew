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
import type { CuttingTest } from '@/types';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    tests: CuttingTest[];
}

const props = withDefaults(defineProps<Props>(), {
    tests: () => [],
});

const emit = defineEmits<{
    edit: [CuttingTest];
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
                <TableHead class="w-24 text-right">
                    {{ t('cuttingTests.table.headers.actions') }}
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="test in rows" :key="test.id">
                <TableCell>{{ formatMoisture(test.moisture) }}</TableCell>
                <TableCell>{{ formatWeight(test.w_reject_nut) }}</TableCell>
                <TableCell>{{ formatWeight(test.w_defective_nut) }}</TableCell>
                <TableCell>{{ formatWeight(test.w_good_kernel) }}</TableCell>
                <TableCell>{{ formatOutturn(test.outturn_rate) }}</TableCell>
                <TableCell class="text-right">
                    <Button
                        v-if="test.id"
                        size="sm"
                        variant="ghost"
                        @click="emit('edit', test)"
                    >
                        {{ t('common.actions.edit') }}
                    </Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
