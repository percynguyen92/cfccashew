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
import type { Container } from '@/types';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    containers: Container[];
}

const props = withDefaults(defineProps<Props>(), {
    containers: () => [],
});

const { t } = useI18n();

const rows = computed(() =>
    (props.containers ?? []).filter((container): container is Container => Boolean(container)),
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
                <TableHead>{{ t('containers.table.headers.net') }}</TableHead>
                <TableHead>{{ t('containers.table.headers.moisture') }}</TableHead>
                <TableHead>{{ t('containers.table.headers.outturn') }}</TableHead>
                <TableHead class="w-24 text-right">
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
                    {{ container.truck || t('common.placeholders.notAvailable') }}
                </TableCell>
                <TableCell>
                    {{
                        formatNumber(container.quantity_of_bags) === '-'
                            ? t('common.placeholders.notAvailable')
                            : formatNumber(container.quantity_of_bags)
                    }}
                </TableCell>
                <TableCell>
                    {{
                        formatWeight(container.w_net) === '-'
                            ? t('common.placeholders.notAvailable')
                            : formatWeight(container.w_net)
                    }}
                </TableCell>
                <TableCell>
                    {{
                        formatMoisture(container.average_moisture) === '-'
                            ? t('common.placeholders.notAvailable')
                            : formatMoisture(container.average_moisture)
                    }}
                </TableCell>
                <TableCell>
                    {{
                        formatOutturn(container.outturn_rate) === '-'
                            ? t('common.placeholders.notAvailable')
                            : formatOutturn(container.outturn_rate)
                    }}
                </TableCell>
                <TableCell class="text-right">
                    <Button
                        v-if="container.id"
                        size="sm"
                        variant="ghost"
                        as-child
                    >
                        <Link :href="`/containers/${container.id}/edit`">
                            {{ t('common.actions.edit') }}
                        </Link>
                    </Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
