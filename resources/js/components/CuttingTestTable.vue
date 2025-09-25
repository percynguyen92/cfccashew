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
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    tests: CuttingTest[];
}

const props = withDefaults(defineProps<Props>(), {
    tests: () => [],
});

const rows = computed(() =>
    (props.tests ?? []).filter((test): test is CuttingTest => Boolean(test)),
);

const formatNumber = (value: number | string | null | undefined, fractionDigits = 0): string => {
    if (value === null || value === undefined) {
        return '-';
    }

    const numeric = typeof value === 'number' ? value : Number.parseFloat(value as string);

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
    return rendered === '-' ? rendered : `${rendered}%`;
};

const formatWeight = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value);
    return rendered === '-' ? rendered : `${rendered}g`;
};

const formatOutturn = (value: number | string | null | undefined): string => {
    const rendered = formatNumber(value, 2);
    return rendered === '-' ? rendered : `${rendered} lbs/80kg`;
};
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Moisture</TableHead>
                <TableHead>Reject Nut</TableHead>
                <TableHead>Defect Nut</TableHead>
                <TableHead>Good Kernel</TableHead>
                <TableHead>Outturn</TableHead>
                <TableHead class="w-24 text-right">Actions</TableHead>
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
                        as-child
                    >
                        <Link :href="`/cutting-tests/${test.id}/edit`">Edit</Link>
                    </Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
