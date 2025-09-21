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
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Edit, Trash2, AlertTriangle, Info } from 'lucide-vue-next';

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
const { breadcrumbs } = useBreadcrumbs();

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
    if (confirm('Are you sure you want to delete this cutting test? This action cannot be undone.')) {
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

const pageTitle = computed(() => {
    return `${props.cutting_test.type_label} - Test #${props.cutting_test.id}`;
});
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
                            {{ cutting_test.type_label }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Created: {{ new Date(cutting_test.created_at).toLocaleString() }}
                        <span v-if="cutting_test.created_at !== cutting_test.updated_at" class="ml-4">
                            Updated: {{ new Date(cutting_test.updated_at).toLocaleString() }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="goBack" class="flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Back to Bill
                    </Button>
                    <Button variant="outline" @click="editCuttingTest" class="flex items-center gap-2">
                        <Edit class="h-4 w-4" />
                        Edit
                    </Button>
                    <Button variant="destructive" @click="deleteCuttingTest" class="flex items-center gap-2">
                        <Trash2 class="h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Validation Alerts -->
            <div v-if="weightDifference > 5 || defectiveNutKernelDifference > 5 || goodKernelDifference > 10" class="space-y-2">
                <Alert v-if="weightDifference > 5" variant="destructive">
                    <AlertTriangle class="h-4 w-4" />
                    <AlertDescription>
                        Weight Alert: Sample weight decreased by {{ weightDifference.toFixed(1) }}g after cutting (threshold: 5g)
                    </AlertDescription>
                </Alert>

                <Alert v-if="defectiveNutKernelDifference > 5" variant="destructive">
                    <AlertTriangle class="h-4 w-4" />
                    <AlertDescription>
                        Defective Ratio Alert: Difference of {{ defectiveNutKernelDifference.toFixed(1) }}g between expected and actual defective kernel weight (threshold: 5g)
                    </AlertDescription>
                </Alert>

                <Alert v-if="goodKernelDifference > 10" variant="destructive">
                    <AlertTriangle class="h-4 w-4" />
                    <AlertDescription>
                        Good Kernel Alert: Difference of {{ goodKernelDifference.toFixed(1) }}g between expected and actual good kernel weight (threshold: 10g)
                    </AlertDescription>
                </Alert>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Test Context -->
                <Card>
                    <CardHeader>
                        <CardTitle>Test Context</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Bill Information -->
                        <div>
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">Associated Bill</h4>
                            <button
                                @click="goToBill"
                                class="flex items-center gap-2 text-blue-600 hover:text-blue-800 hover:underline"
                            >
                                <span class="font-medium">
                                    {{ cutting_test.bill?.bill_number || `Bill #${cutting_test.bill_id}` }}
                                </span>
                            </button>
                            <div v-if="cutting_test.bill" class="text-sm text-muted-foreground mt-1">
                                <span v-if="cutting_test.bill.seller">Seller: {{ cutting_test.bill.seller }}</span>
                                <span v-if="cutting_test.bill.buyer" class="ml-4">Buyer: {{ cutting_test.bill.buyer }}</span>
                            </div>
                        </div>

                        <Separator />

                        <!-- Container Information -->
                        <div>
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">Container Association</h4>
                            <div v-if="cutting_test.is_final_sample" class="text-sm text-muted-foreground italic">
                                Final Sample (not associated with specific container)
                            </div>
                            <div v-else-if="cutting_test.container">
                                <button
                                    @click="goToContainer"
                                    class="flex items-center gap-2 text-blue-600 hover:text-blue-800 hover:underline"
                                >
                                    <span class="font-medium">
                                        {{ cutting_test.container.container_number || `Container #${cutting_test.container.id}` }}
                                    </span>
                                </button>
                                <div v-if="cutting_test.container.truck" class="text-sm text-muted-foreground mt-1">
                                    Truck: {{ cutting_test.container.truck }}
                                </div>
                            </div>
                            <div v-else class="text-sm text-muted-foreground">
                                Container #{{ cutting_test.container_id }}
                            </div>
                        </div>

                        <Separator />

                        <!-- Test Type -->
                        <div>
                            <h4 class="text-sm font-medium text-muted-foreground mb-2">Test Type</h4>
                            <Badge :variant="getTestTypeBadgeVariant(cutting_test.type)" class="text-sm">
                                {{ cutting_test.type_label }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- Basic Measurements -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Measurements</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">Sample Weight</h4>
                                <p class="text-lg font-semibold">{{ cutting_test.sample_weight }}g</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">Moisture</h4>
                                <p class="text-lg font-semibold font-mono">
                                    {{ cutting_test.moisture_formatted || 'Not measured' }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">Nut Count</h4>
                                <p class="text-lg font-semibold">
                                    {{ cutting_test.nut_count || 'Not counted' }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-1">Outturn Rate</h4>
                                <p class="text-lg font-semibold font-mono">
                                    {{ cutting_test.outturn_rate_formatted || 'Not calculated' }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Weight Breakdown -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Weight Breakdown</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <!-- Reject Weights -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-muted-foreground">Rejected Material</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Reject Nut Weight:</span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_reject_nut ? `${cutting_test.w_reject_nut}g` : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Defective Weights -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-muted-foreground">Defective Material</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Defective Nut:</span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_defective_nut ? `${cutting_test.w_defective_nut}g` : '-' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Defective Kernel:</span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_defective_kernel ? `${cutting_test.w_defective_kernel}g` : '-' }}
                                        </span>
                                    </div>
                                    <div v-if="cutting_test.defective_ratio" class="flex justify-between items-center pt-1 border-t">
                                        <span class="text-sm">Ratio (nut/kernel):</span>
                                        <span class="font-semibold font-mono text-blue-600">
                                            {{ cutting_test.defective_ratio.formatted }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Good Weights -->
                            <div class="space-y-3">
                                <h4 class="text-sm font-medium text-muted-foreground">Good Material</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Good Kernel Weight:</span>
                                        <span class="font-semibold font-mono text-green-600">
                                            {{ cutting_test.w_good_kernel ? `${cutting_test.w_good_kernel}g` : '-' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm">Sample After Cut:</span>
                                        <span class="font-semibold font-mono">
                                            {{ cutting_test.w_sample_after_cut ? `${cutting_test.w_sample_after_cut}g` : '-' }}
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
                        <CardTitle>Notes</CardTitle>
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
                            Calculation Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Alert>
                            <Info class="h-4 w-4" />
                            <AlertDescription>
                                <div class="space-y-1 text-sm">
                                    <p><strong>Outturn Rate Formula:</strong> (Defective Kernel Weight ÷ 2 + Good Kernel Weight) × 80 ÷ 453.6</p>
                                    <p><strong>Defective Ratio:</strong> Defective Nut Weight ÷ Defective Kernel Weight × 2</p>
                                    <p><strong>Alert Thresholds:</strong> Sample weight difference > 5g, Defective ratio difference > 5g, Good kernel difference > 10g</p>
                                </div>
                            </AlertDescription>
                        </Alert>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>