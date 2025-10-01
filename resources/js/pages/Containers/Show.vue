<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Container, type CuttingTest } from '@/types';
import * as containerRoutes from '@/routes/containers';
import * as billRoutes from '@/routes/bills';
import * as cuttingTestRoutes from '@/routes/cutting-tests';
import { Head, router } from '@inertiajs/vue3';
import {
    Package,
    FileText,
    Truck,
    Scale,
    Droplets,
    Target,
    Plus,
    ArrowLeft,
    Edit,
    AlertTriangle
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    container: Container;
}

const props = defineProps<Props>();

const { t } = useI18n();
const placeholder = computed(() => t('common.placeholders.notAvailable'));

const { breadcrumbs } = useBreadcrumbs();

const containerIdentifier = computed(
    () => props.container.container_number || `#${props.container.id}`,
);

// Format weight display
const formatWeight = (weight: number | string | null): string => {
    if (weight === null || weight === undefined) return placeholder.value;
    const numWeight = typeof weight === 'string' ? parseFloat(weight) : weight;
    if (isNaN(numWeight)) return placeholder.value;
    return numWeight.toLocaleString();
};

// Format decimal weight display
const formatDecimalWeight = (weight: number | string | null): string => {
    if (weight === null || weight === undefined) return placeholder.value;
    const numWeight = typeof weight === 'string' ? parseFloat(weight) : weight;
    if (isNaN(numWeight)) return placeholder.value;
    return numWeight.toFixed(2);
};

// Format moisture display
const formatMoisture = (moisture: number | string | null | undefined): string => {
    if (moisture === null || moisture === undefined) return placeholder.value;
    const numMoisture = typeof moisture === 'string' ? parseFloat(moisture) : moisture;
    if (isNaN(numMoisture)) return placeholder.value;
    return t('containers.show.moistureValue', {
        value: numMoisture.toFixed(1),
    });
};

// Format outurn display
const formatOuturn = (outurn: number | string | null | undefined): string => {
    if (outurn === null || outurn === undefined) return placeholder.value;
    const numOuturn = typeof outurn === 'string' ? parseFloat(outurn) : outurn;
    if (isNaN(numOuturn)) return placeholder.value;
    return t('containers.index.table.outturnValue', {
        value: numOuturn.toFixed(2),
    });
};

// Format defective ratio
const formatDefectiveRatio = (test: CuttingTest): string => {
    if (!test.w_defective_nut || !test.w_defective_kernel)
        return placeholder.value;
    const ratio = test.w_defective_nut / 2;
    return t('containers.show.defectiveRatio', {
        nut: test.w_defective_nut,
        kernel: ratio.toFixed(1),
    });
};

// Get cutting test type label
const getTestTypeLabel = (type: number): string => {
    const labels: Record<number, string> = {
        1: t('containers.show.cuttingTests.types.finalFirst'),
        2: t('containers.show.cuttingTests.types.finalSecond'),
        3: t('containers.show.cuttingTests.types.finalThird'),
        4: t('containers.show.cuttingTests.types.container'),
    };
    return labels[type] ?? t('containers.show.cuttingTests.types.generic', { type });
};

// Get cutting test type badge variant
const getTestTypeBadgeVariant = (type: number): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (type === 4) return 'default';
    return 'secondary';
};

// Navigate functions
const goBack = () => {
    router.visit(containerRoutes.index.url());
};

const editContainer = () => {
    // Use container number if available, otherwise fall back to ID
    const identifier = props.container.container_number || props.container.id;
    router.visit(containerRoutes.edit.url(identifier.toString()));
};

const viewBill = () => {
    if (props.container.bill) {
        router.visit(billRoutes.show.url(props.container.bill.slug || props.container.bill.id));
    }
};

const addCuttingTest = () => {
    router.visit(cuttingTestRoutes.create.url({ query: { container_id: props.container.id, bill_id: props.container.bill_id } }));
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
const hasWeightDiscrepancies = computed(() => {
    const container = props.container;
    const discrepancies = [];

    // Check if calculated weights match expected values
    if (container.w_total && container.w_truck && container.w_container) {
        const expectedGross = container.w_total - container.w_truck - container.w_container;
        if (container.w_gross && Math.abs(container.w_gross - expectedGross) > 1) {
            discrepancies.push('Gross weight calculation discrepancy detected');
        }
    }

    // Check if net weight calculation is correct
    if (container.w_gross && container.w_dunnage_dribag && container.w_tare) {
        const expectedNet = container.w_gross - container.w_dunnage_dribag - container.w_tare;
        if (container.w_net && Math.abs(container.w_net - expectedNet) > 1) {
            discrepancies.push('Net weight calculation discrepancy detected');
        }
    }

    return discrepancies;
});
</script>

<template>

    <Head :title="t('containers.show.title', { identifier: containerIdentifier })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div class="flex items-center gap-2">
                        <Package class="h-6 w-6" />
                        <h1 class="text-2xl font-semibold">
                            {{
                                t('containers.show.heading', {
                                    identifier: containerIdentifier,
                                })
                            }}
                        </h1>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="editContainer">
                        <Edit class="h-4 w-4 mr-2" />
                        {{ t('containers.show.actions.edit') }}
                    </Button>
                    <Button @click="addCuttingTest">
                        <Plus class="h-4 w-4 mr-2" />
                        {{ t('containers.show.actions.addCuttingTest') }}
                    </Button>
                </div>
            </div>

            <!-- Weight Discrepancy Alerts -->
            <div v-if="hasWeightDiscrepancies.length > 0" class="space-y-2">
                <div v-for="discrepancy in hasWeightDiscrepancies" :key="discrepancy"
                    class="flex items-start gap-2 rounded-lg border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>{{ discrepancy }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Container Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Package class="h-5 w-5" />
                                {{ t('containers.show.sections.containerInfo.title') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">
                                        {{ t('containers.form.fields.containerNumber.label') }}
                                    </label>
                                    <p class="text-lg font-medium">
                                        {{
                                            container.container_number ||
                                            placeholder
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">
                                        {{ t('containers.form.fields.truck.label') }}
                                    </label>
                                    <p class="text-lg font-medium flex items-center gap-2">
                                        <Truck class="h-4 w-4" />
                                        {{ container.truck || placeholder }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">
                                        {{
                                            t(
                                                'containers.form.fields.quantityOfBags.label',
                                            )
                                        }}
                                    </label>
                                    <p class="text-lg font-medium">
                                        {{
                                            container.quantity_of_bags ||
                                            placeholder
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">
                                        {{
                                            t(
                                                'containers.form.fields.juteBagWeight.label',
                                            )
                                        }}
                                    </label>
                                    <p class="text-lg font-medium">
                                        {{ formatDecimalWeight(container.w_jute_bag) }}
                                        {{ t('containers.show.units.kilogram') }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">
                                        {{ t('containers.form.fields.containerCondition.label') }}
                                    </label>
                                    <p class="text-lg font-medium flex items-center gap-2">
                                        <span :class="getConditionColor(container.container_condition)"
                                            class="w-3 h-3 rounded-full"></span>
                                        {{ container.container_condition || placeholder }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">
                                        {{ t('containers.form.fields.sealCondition.label') }}
                                    </label>
                                    <p class="text-lg font-medium flex items-center gap-2">
                                        <span :class="getConditionColor(container.seal_condition)"
                                            class="w-3 h-3 rounded-full"></span>
                                        {{ container.seal_condition || placeholder }}
                                    </p>
                                </div>
                            </div>

                            <Separator />

                            <!-- Weight Information -->
                            <div>
                                <h4 class="font-medium mb-3 flex items-center gap-2">
                                    <Scale class="h-4 w-4" />
                                    {{ t('containers.show.sections.weightInfo.title') }}
                                </h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">
                                            {{ t('containers.form.fields.totalWeight.label') }}
                                        </label>
                                        <p class="font-medium">
                                            {{ formatWeight(container.w_total) }}
                                            {{ t('containers.show.units.kilogram') }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">
                                            {{ t('containers.form.fields.truckWeight.label') }}
                                        </label>
                                        <p class="font-medium">
                                            {{ formatWeight(container.w_truck) }}
                                            {{ t('containers.show.units.kilogram') }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">
                                            {{ t('containers.form.fields.containerWeight.label') }}
                                        </label>
                                        <p class="font-medium">
                                            {{ formatWeight(container.w_container) }}
                                            {{ t('containers.show.units.kilogram') }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">
                                            {{ t('containers.show.fields.grossWeight') }}
                                        </label>
                                        <p class="font-medium">
                                            {{ formatWeight(container.w_gross) }}
                                            {{ t('containers.show.units.kilogram') }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">
                                            {{ t('containers.form.fields.dunnageWeight.label') }}
                                        </label>
                                        <p class="font-medium">
                                            {{ formatWeight(container.w_dunnage_dribag) }}
                                            {{ t('containers.show.units.kilogram') }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">
                                            {{ t('containers.show.fields.tareWeight') }}
                                        </label>
                                        <p class="font-medium">
                                            {{ formatDecimalWeight(container.w_tare) }}
                                            {{ t('containers.show.units.kilogram') }}
                                        </p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-sm font-medium text-muted-foreground">
                                            {{ t('containers.show.fields.netWeight') }}
                                        </label>
                                        <p class="text-lg font-semibold text-green-600">
                                            {{ formatDecimalWeight(container.w_net) }}
                                            {{ t('containers.show.units.kilogram') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="container.note">
                                <Separator />
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">
                                        {{ t('containers.show.fields.notes') }}
                                    </label>
                                    <p class="mt-1">{{ container.note }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Cutting Tests -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Target class="h-5 w-5" />
                                    {{ t('containers.show.cuttingTests.title') }}
                                </div>
                                <Button size="sm" @click="addCuttingTest">
                                    <Plus class="h-4 w-4 mr-2" />
                                    {{ t('containers.show.cuttingTests.add') }}
                                </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="container.cutting_tests && container.cutting_tests.length > 0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>
                                                {{ t('containers.show.cuttingTests.headers.type') }}
                                            </TableHead>
                                            <TableHead>
                                                {{ t('containers.show.cuttingTests.headers.moisture') }}
                                            </TableHead>
                                            <TableHead>
                                                {{ t('containers.show.cuttingTests.headers.sampleWeight') }}
                                            </TableHead>
                                            <TableHead>
                                                {{ t('containers.show.cuttingTests.headers.nutCount') }}
                                            </TableHead>
                                            <TableHead>
                                                {{ t('containers.show.cuttingTests.headers.defectiveRatio') }}
                                            </TableHead>
                                            <TableHead>
                                                {{ t('containers.show.cuttingTests.headers.outturn') }}
                                            </TableHead>
                                            <TableHead>
                                                {{ t('containers.show.cuttingTests.headers.date') }}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="test in container.cutting_tests" :key="test.id">
                                            <TableCell>
                                                <Badge :variant="getTestTypeBadgeVariant(test.type)">
                                                    {{ getTestTypeLabel(test.type) }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <span :class="test.moisture && test.moisture > 11
                                                    ? 'text-red-600 font-medium'
                                                    : ''">
                                                    {{ formatMoisture(test.moisture) }}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                {{
                                                    t(
                                                        'containers.show.cuttingTests.sampleWeightValue',
                                                        {
                                                            value: test.sample_weight,
                                                        },
                                                    )
                                                }}
                                            </TableCell>
                                            <TableCell>
                                                {{ test.nut_count || placeholder }}
                                            </TableCell>
                                            <TableCell>
                                                {{ formatDefectiveRatio(test) }}
                                            </TableCell>
                                            <TableCell>
                                                {{ formatOuturn(test.outturn_rate) }}
                                            </TableCell>
                                            <TableCell>
                                                <div class="text-sm text-muted-foreground">
                                                    {{ new Date(test.created_at).toLocaleDateString() }}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div class="text-center py-8 text-muted-foreground" v-else>
                                <Target class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p class="text-lg font-medium mb-2">
                                    {{ t('containers.show.cuttingTests.empty.title') }}
                                </p>
                                <p class="mb-4">
                                    {{ t('containers.show.cuttingTests.empty.subtitle') }}
                                </p>
                                <Button @click="addCuttingTest">
                                    <Plus class="h-4 w-4 mr-2" />
                                    {{ t('containers.show.cuttingTests.empty.action') }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Bill Information -->
                    <Card v-if="container.bill">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                {{ t('containers.show.billInfo.title') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">
                                    {{ t('bills.form.fields.billNumber.label') }}
                                </label>
                                <p class="text-lg font-medium">
                                    {{
                                        container.bill.bill_number ||
                                        t('containers.index.table.billNumberFallback', {
                                            id: container.bill.id,
                                        })
                                    }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">
                                    {{ t('bills.form.fields.seller.label') }}
                                </label>
                                <p class="font-medium">
                                    {{ container.bill.seller || placeholder }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">
                                    {{ t('bills.form.fields.buyer.label') }}
                                </label>
                                <p class="font-medium">
                                    {{ container.bill.buyer || placeholder }}
                                </p>
                            </div>
                            <Button variant="outline" class="w-full" @click="viewBill">
                                <FileText class="h-4 w-4 mr-2" />
                                {{ t('containers.show.billInfo.view') }}
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Quality Summary -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Droplets class="h-5 w-5" />
                                {{ t('containers.show.quality.title') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">
                                    {{ t('containers.show.quality.averageMoisture') }}
                                </label>
                                <p class="text-lg font-medium">
                                    <span :class="container.average_moisture && container.average_moisture > 11
                                        ? 'text-red-600'
                                        : 'text-green-600'">
                                        {{ formatMoisture(container.average_moisture) }}
                                    </span>
                                </p>
                                <p v-if="container.average_moisture && container.average_moisture > 11"
                                    class="text-sm text-red-600 mt-1">
                                    {{ t('containers.show.quality.moistureWarning') }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">
                                    {{ t('containers.show.quality.outturn') }}
                                </label>
                                <p class="text-lg font-medium text-blue-600">
                                    {{ formatOuturn(container.outturn_rate) }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">
                                    {{ t('containers.show.quality.testsCount') }}
                                </label>
                                <p class="text-lg font-medium">
                                    {{
                                        t('containers.show.quality.testsCountValue', {
                                            count: container.cutting_tests?.length || 0,
                                        })
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
