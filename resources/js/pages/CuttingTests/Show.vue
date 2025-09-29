<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Edit, Trash2, AlertTriangle, Info } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

interface CuttingTest {
    id: number;
    bill_id: number;
    container_id: number | null;
    type: 1 | 2 | 3 | 4;
    type_label: string;
    moisture: number | null;
    moisture_formatted: string | null;
    sample_weight: number;
    nut_count: number | null;
    w_reject_nut: number | null;
    w_defective_nut: number | null;
    w_defective_kernel: number | null;
    w_good_kernel: number | null;
    w_sample_after_cut: number | null;
    outturn_rate: number | null;
    outturn_rate_formatted: string | null;
    note: string | null;
    defective_ratio?: {
        defective_nut: number;
        defective_kernel: number;
        ratio: number;
        formatted: string;
    };
    is_final_sample: boolean;
    is_container_test: boolean;
    bill?: {
        id: number;
        bill_number: string | null;
        seller: string | null;
        buyer: string | null;
    };
    container?: {
        id: number;
        container_number: string | null;
        truck: string | null;
    };
    created_at: string;
    updated_at: string;
}

interface Props {
    cutting_test: CuttingTest;
}

const props = defineProps<Props>();
const { t } = useI18n();
const { breadcrumbs } = useBreadcrumbs();
const placeholder = computed(() => t('common.placeholders.notAvailable'));

const typeLabel = computed(() => {
    const map: Record<number, string> = {
        1: t('cuttingTests.show.types.finalFirst'),
        2: t('cuttingTests.show.types.finalSecond'),
        3: t('cuttingTests.show.types.finalThird'),
        4: t('cuttingTests.show.types.container'),
    };

    return map[props.cutting_test.type] ?? t('cuttingTests.show.types.generic', {
        type: props.cutting_test.type,
    });
});

// Computed values for validation alerts
const weightDifference = computed(() => {
    const original = props.cutting_test.sample_weight || 0;
    const after = props.cutting_test.w_sample_after_cut || 0;
    return original - after;
});

const defectiveNutKernelDifference = computed(() => {
    const defectiveNut = props.cutting_test.w_defective_nut || 0;
    const defectiveKernel = props.cutting_test.w_defective_kernel || 0;
    return defectiveNut / 3.3 - defectiveKernel;
});

const goodKernelDifference = computed(() => {
    const sampleWeight = props.cutting_test.sample_weight || 0;
    const rejectNut = props.cutting_test.w_reject_nut || 0;
    const defectiveNut = props.cutting_test.w_defective_nut || 0;
    const goodKernel = props.cutting_test.w_good_kernel || 0;
    return (sampleWeight - rejectNut - defectiveNut) / 3.3 - goodKernel;
});

// Navigation functions
function goBack() {
    router.visit(`/bills/${props.cutting_test.bill_id}`);
}

function editCuttingTest() {
    router.visit(`/cutting-tests/${props.cutting_test.id}/edit`);
}

function deleteCuttingTest() {
    if (confirm(t('cuttingTests.show.dialog.delete.confirm'))) {
        router.delete(`/cutting-tests/${props.cutting_test.id}`);
    }
}

function goToBill() {
    router.visit(`/bills/${props.cutting_test.bill_id}`);
}

function goToContainer() {
    if (props.cutting_test.container) {
        const identifier = props.cutting_test.container.container_number || props.cutting_test.container.id;
        router.visit(`/containers/${identifier}`);
    }
}

// Get badge variant for test type
function getTestTypeBadgeVariant(type: number) {
    switch (type) {
        case 1:
        case 2:
        case 3:
            return 'default'; // Final samples - blue
        case 4:
            return 'secondary'; // Container cut - gray
        default:
            return 'outline';
    }
}

const pageTitle = computed(() =>
    t('cuttingTests.show.pageTitle', {
        type: typeLabel.value,
        id: props.cutting_test.id,
    }),
);
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-semibold">{{ pageTitle }}</h1>
                        <Badge :variant="getTestTypeBadgeVariant(cutting_test.type)">
                            {{ typeLabel }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{
                            t('cuttingTests.show.meta.created', {
                                date: new Date(cutting_test.created_at).toLocaleString(),
                            })
                        }}
                        <span
                            v-if="cutting_test.created_at !== cutting_test.updated_at"
                            class="ml-4"
                        >
                            {{
                                t('cuttingTests.show.meta.updated', {
                                    date: new Date(cutting_test.updated_at).toLocaleString(),
                                })
                            }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="goBack" class="flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        {{ t('cuttingTests.show.actions.back') }}
                    </Button>
                    <Button variant="outline" @click="editCuttingTest" class="flex items-center gap-2">
                        <Edit class="h-4 w-4" />
                        {{ t('cuttingTests.show.actions.edit') }}
                    </Button>
                    <Button variant="destructive" @click="deleteCuttingTest" class="flex items-center gap-2">
                        <Trash2 class="h-4 w-4" />
                        {{ t('cuttingTests.show.actions.delete') }}
                    </Button>
                </div>
            </div>

            <!-- Validation Alerts -->
            <div v-if="weightDifference > 5 || defectiveNutKernelDifference > 5 || goodKernelDifference > 10" class="space-y-2">
                <div
                    v-if="weightDifference > 5"
                    class="flex items-start gap-2 rounded-lg border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        {{
                            t('cuttingTests.show.alerts.weight', {
                                difference: weightDifference.toFixed(1),
                            })
                        }}
                    </span>
                </div>

                <div
                    v-if="defectiveNutKernelDifference > 5"
                    class="flex items-start gap-2 rounded-lg border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        {{
                            t('cuttingTests.show.alerts.defectiveRatio', {
                                difference: defectiveNutKernelDifference.toFixed(1),
                            })
                        }}
                    </span>
                </div>

                <div
                    v-if="goodKernelDifference > 10"
                    class="flex items-start gap-2 rounded-lg border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        {{
                            t('cuttingTests.show.alerts.goodKernel', {
                                difference: goodKernelDifference.toFixed(1),
                            })
                        }}
                    </span>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Test Context -->
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {{ t('cuttingTests.show.sections.context.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Bill Information -->
                        <div>
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">
                                {{ t('cuttingTests.show.sections.context.bill.title') }}
                            </h4>
                            <button
                                @click="goToBill"
                                class="flex items-center gap-2 text-blue-600 hover:text-blue-800 hover:underline"
                            >
                                <span class="font-medium">
                                    {{ cutting_test.bill?.bill_number || t('cuttingTests.index.table.billFallback', { id: cutting_test.bill_id }) }}
                                </span>
                            </button>
                            <div v-if="cutting_test.bill" class="text-sm text-muted-foreground mt-1">
                                <span v-if="cutting_test.bill.seller">
                                    {{
                                        t('cuttingTests.show.sections.context.bill.seller', {
                                            value: cutting_test.bill.seller,
                                        })
                                    }}
                                </span>
                                <span v-if="cutting_test.bill.buyer" class="ml-4">
                                    {{
                                        t('cuttingTests.show.sections.context.bill.buyer', {
                                            value: cutting_test.bill.buyer,
                                        })
                                    }}
                                </span>
                            </div>
                        </div>

                        <Separator />

                        <!-- Container Information -->
                        <div>
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">
                                {{ t('cuttingTests.show.sections.context.container.title') }}
                            </h4>
                            <div v-if="cutting_test.is_final_sample" class="text-sm text-muted-foreground italic">
                                {{ t('cuttingTests.show.sections.context.container.finalSample') }}
                            </div>
                            <div v-else-if="cutting_test.container">
                                <button
                                    @click="goToContainer"
                                    class="flex items-center gap-2 text-blue-600 hover:text-blue-800 hover:underline"
                                >
                                    <span class="font-medium">
                                        {{ cutting_test.container.container_number || t('cuttingTests.index.table.containerFallback', { id: cutting_test.container.id }) }}
                                    </span>
                                </button>
                                <div v-if="cutting_test.container.truck" class="text-sm text-muted-foreground mt-1">
                                    {{
                                        t('cuttingTests.show.sections.context.container.truck', {
                                            value: cutting_test.container.truck,
                                        })
                                    }}
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">
                                {{
                                    t('cuttingTests.show.sections.context.container.fallback', {
                                        id: cutting_test.container_id,
                                    })
                                }}
                            </div>
                        </div>

                        <Separator />

                        <!-- Test Type -->
                        <div>
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">
                                {{ t('cuttingTests.show.sections.context.testType') }}
                            </h4>
                            <Badge :variant="getTestTypeBadgeVariant(cutting_test.type)" class="text-sm">
                                {{ typeLabel }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- Basic Measurements -->
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {{ t('cuttingTests.show.sections.measurements.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">
                                    {{ t('cuttingTests.show.sections.measurements.sampleWeight') }}
                                </h4>
                                <p class="text-lg font-semibold">
                                    {{ t('cuttingTests.show.units.grams', { value: cutting_test.sample_weight }) }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">
                                    {{ t('cuttingTests.show.sections.measurements.moisture') }}
                                </h4>
                                <p class="text-lg font-semibold font-mono">
                                    {{ cutting_test.moisture_formatted || t('cuttingTests.show.sections.measurements.notMeasured') }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">
                                    {{ t('cuttingTests.show.sections.measurements.nutCount') }}
                                </h4>
                                <p class="text-lg font-semibold">
                                    {{ cutting_test.nut_count || t('cuttingTests.show.sections.measurements.notCounted') }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">
                                    {{ t('cuttingTests.show.sections.measurements.outturn') }}
                                </h4>
                                <p class="text-lg font-semibold font-mono">
                                    {{ cutting_test.outturn_rate_formatted || t('cuttingTests.show.sections.measurements.notCalculated') }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Weight Breakdown -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>
                            {{ t('cuttingTests.show.sections.weights.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <!-- Reject Weights -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-muted-foreground">
                                    {{ t('cuttingTests.show.sections.weights.reject.title') }}
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">
                                            {{ t('cuttingTests.show.sections.weights.reject.nut') }}
                                        </span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_reject_nut ? t('cuttingTests.show.units.grams', { value: cutting_test.w_reject_nut }) : placeholder }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Defective Weights -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-muted-foreground">
                                    {{ t('cuttingTests.show.sections.weights.defective.title') }}
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">
                                            {{ t('cuttingTests.show.sections.weights.defective.nut') }}
                                        </span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_defective_nut ? t('cuttingTests.show.units.grams', { value: cutting_test.w_defective_nut }) : placeholder }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">
                                            {{ t('cuttingTests.show.sections.weights.defective.kernel') }}
                                        </span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_defective_kernel ? t('cuttingTests.show.units.grams', { value: cutting_test.w_defective_kernel }) : placeholder }}
                                        </span>
                                    </div>
                                    <div v-if="cutting_test.defective_ratio" class="flex justify-between items-center pt-1 border-t">
                                        <span class="text-sm">
                                            {{ t('cuttingTests.show.sections.weights.defective.ratioLabel') }}
                                        </span>
                                        <span class="font-semibold font-mono text-blue-600">
                                            {{ cutting_test.defective_ratio.formatted }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Good Weights -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-muted-foreground">
                                    {{ t('cuttingTests.show.sections.weights.good.title') }}
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">
                                            {{ t('cuttingTests.show.sections.weights.good.kernel') }}
                                        </span>
                                        <span class="font-semibold font-mono text-green-600">
                                            {{ cutting_test.w_good_kernel ? t('cuttingTests.show.units.grams', { value: cutting_test.w_good_kernel }) : placeholder }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">
                                            {{ t('cuttingTests.show.sections.weights.good.sampleAfter') }}
                                        </span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_sample_after_cut ? t('cuttingTests.show.units.grams', { value: cutting_test.w_sample_after_cut }) : placeholder }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Notes -->
                <Card v-if="cutting_test.note" class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>
                            {{ t('cuttingTests.show.sections.notes.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="p-3 bg-muted/50 rounded-lg">
                            <p class="text-sm whitespace-pre-wrap">{{ cutting_test.note }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Calculation Information -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Info class="h-4 w-4" />
                            {{ t('cuttingTests.show.sections.calculations.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Alert>
                            <Info class="h-4 w-4" />
                            <AlertDescription>
                                <div class="space-y-1 text-sm">
                                    <p>
                                        <strong>{{ t('cuttingTests.show.sections.calculations.formulas.outturn.title') }}</strong>
                                        {{ t('cuttingTests.show.sections.calculations.formulas.outturn.body') }}
                                    </p>
                                    <p>
                                        <strong>{{ t('cuttingTests.show.sections.calculations.formulas.defective.title') }}</strong>
                                        {{ t('cuttingTests.show.sections.calculations.formulas.defective.body') }}
                                    </p>
                                    <p>
                                        <strong>{{ t('cuttingTests.show.sections.calculations.thresholds.title') }}</strong>
                                        {{ t('cuttingTests.show.sections.calculations.thresholds.body') }}
                                    </p>
                                </div>
                            </AlertDescription>
                        </Alert>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
