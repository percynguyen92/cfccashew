<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Save } from 'lucide-vue-next';
import { computed, watch } from 'vue';

interface Bill {
    id: number;
    bill_number: string | null;
    seller: string | null;
    buyer: string | null;
}

interface Container {
    id: number;
    container_number: string | null;
    truck: string | null;
}

interface Props {
    bill_id?: number;
    container_id?: number;
    bill?: Bill;
    container?: Container;
}

const props = defineProps<Props>();
const { breadcrumbs } = useBreadcrumbs();

// Form data
const form = useForm({
    bill_id: props.bill_id || '',
    container_id: props.container_id || '',
    type: props.container_id ? 4 : 1, // Default to container test if container_id provided, otherwise final sample
    moisture: '',
    sample_weight: 1000,
    nut_count: '',
    w_reject_nut: '',
    w_defective_nut: '',
    w_defective_kernel: '',
    w_good_kernel: '',
    w_sample_after_cut: '',
    note: '',
});

// Computed values for form validation alerts
const weightDifference = computed(() => {
    const original = Number(form.sample_weight) || 0;
    const after = Number(form.w_sample_after_cut) || 0;
    return original - after;
});

const defectiveNutKernelDifference = computed(() => {
    const defectiveNut = Number(form.w_defective_nut) || 0;
    const defectiveKernel = Number(form.w_defective_kernel) || 0;
    return defectiveNut / 3.3 - defectiveKernel;
});

const goodKernelDifference = computed(() => {
    const sampleWeight = Number(form.sample_weight) || 0;
    const rejectNut = Number(form.w_reject_nut) || 0;
    const defectiveNut = Number(form.w_defective_nut) || 0;
    const goodKernel = Number(form.w_good_kernel) || 0;
    return (sampleWeight - rejectNut - defectiveNut) / 3.3 - goodKernel;
});

const calculatedOutturnRate = computed(() => {
    const defectiveKernel = Number(form.w_defective_kernel) || 0;
    const goodKernel = Number(form.w_good_kernel) || 0;

    if (defectiveKernel > 0 || goodKernel > 0) {
        return (((defectiveKernel / 2 + goodKernel) * 80) / 453.6).toFixed(2);
    }
    return null;
});

// Form context computed values
const isFinalSample = computed(() => [1, 2, 3].includes(form.type));
const isContainerTest = computed(() => form.type === 4);

const testTypeOptions = [
    { value: 1, label: 'Final Sample 1st Cut' },
    { value: 2, label: 'Final Sample 2nd Cut' },
    { value: 3, label: 'Final Sample 3rd Cut' },
    { value: 4, label: 'Container Cut' },
];

// Watch for type changes to validate container association
watch(
    () => form.type,
    (newType) => {
        if ([1, 2, 3].includes(newType)) {
            // Final samples should not have container
            form.container_id = '';
        } else if (newType === 4 && !form.container_id) {
            // Container tests need container_id
            if (props.container_id) {
                form.container_id = props.container_id;
            }
        }
    },
);

// Submit form
function submit() {
    form.post('/cutting-tests', {
        onSuccess: () => {
            // Will redirect to bill show page from controller
        },
    });
}

// Cancel and go back
function cancel() {
    if (props.bill_id) {
        router.visit(`/bills/${props.bill_id}`);
    } else {
        router.visit('/cutting-tests');
    }
}

// Get page title based on context
const pageTitle = computed(() => {
    if (props.container) {
        return `Add Container Test - ${props.container.container_number || `Container #${props.container.id}`}`;
    }
    if (props.bill) {
        return `Add Cutting Test - ${props.bill.bill_number || `Bill #${props.bill.id}`}`;
    }
    return 'Add Cutting Test';
});

// Get test type label
function getTestTypeLabel(type: number) {
    const option = testTypeOptions.find((opt) => opt.value === type);
    return option?.label || `Type ${type}`;
}
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">{{ pageTitle }}</h1>
                    <p
                        v-if="props.bill"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        Bill:
                        {{ props.bill.bill_number || `#${props.bill.id}` }}
                        <span v-if="props.bill.seller" class="ml-2">
                            | Seller: {{ props.bill.seller }}
                        </span>
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="cancel"
                    class="flex items-center gap-2"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Cancel
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Test Type and Context -->
                <Card>
                    <CardHeader>
                        <CardTitle>Test Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Test Type -->
                            <div class="space-y-2">
                                <Label for="type">Test Type *</Label>
                                <Select v-model="form.type" required>
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="
                                                getTestTypeLabel(form.type)
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in testTypeOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.type"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <!-- Sample Weight -->
                            <div class="space-y-2">
                                <Label for="sample_weight"
                                    >Sample Weight (grams) *</Label
                                >
                                <Input
                                    id="sample_weight"
                                    v-model="form.sample_weight"
                                    type="number"
                                    min="1"
                                    max="65535"
                                    required
                                />
                                <p
                                    v-if="form.errors.sample_weight"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.sample_weight }}
                                </p>
                            </div>
                        </div>

                        <!-- Context Information -->
                        <div
                            v-if="isFinalSample"
                            class="rounded-lg bg-blue-50 p-3 dark:bg-blue-950/20"
                        >
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Final Sample Test:</strong> This test
                                will be associated with the bill but not with
                                any specific container.
                            </p>
                        </div>

                        <div
                            v-if="isContainerTest && props.container"
                            class="rounded-lg bg-green-50 p-3 dark:bg-green-950/20"
                        >
                            <p
                                class="text-sm text-green-800 dark:text-green-200"
                            >
                                <strong>Container Test:</strong>
                                Testing container
                                {{
                                    props.container.container_number ||
                                    `#${props.container.id}`
                                }}
                                <span v-if="props.container.truck">
                                    (Truck: {{ props.container.truck }})</span
                                >
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Basic Measurements -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Measurements</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Moisture -->
                            <div class="space-y-2">
                                <Label for="moisture">Moisture (%)</Label>
                                <Input
                                    id="moisture"
                                    v-model="form.moisture"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    placeholder="e.g., 12.5"
                                />
                                <p
                                    v-if="form.errors.moisture"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.moisture }}
                                </p>
                            </div>

                            <!-- Nut Count -->
                            <div class="space-y-2">
                                <Label for="nut_count">Nut Count</Label>
                                <Input
                                    id="nut_count"
                                    v-model="form.nut_count"
                                    type="number"
                                    min="0"
                                    max="65535"
                                    placeholder="Total number of nuts"
                                />
                                <p
                                    v-if="form.errors.nut_count"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.nut_count }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Weight Measurements -->
                <Card>
                    <CardHeader>
                        <CardTitle>Weight Measurements (grams)</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Reject Nut Weight -->
                            <div class="space-y-2">
                                <Label for="w_reject_nut"
                                    >Reject Nut Weight</Label
                                >
                                <Input
                                    id="w_reject_nut"
                                    v-model="form.w_reject_nut"
                                    type="number"
                                    min="0"
                                    max="65535"
                                />
                                <p
                                    v-if="form.errors.w_reject_nut"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.w_reject_nut }}
                                </p>
                            </div>

                            <!-- Defective Nut Weight -->
                            <div class="space-y-2">
                                <Label for="w_defective_nut"
                                    >Defective Nut Weight</Label
                                >
                                <Input
                                    id="w_defective_nut"
                                    v-model="form.w_defective_nut"
                                    type="number"
                                    min="0"
                                    max="65535"
                                />
                                <p
                                    v-if="form.errors.w_defective_nut"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.w_defective_nut }}
                                </p>
                            </div>

                            <!-- Defective Kernel Weight -->
                            <div class="space-y-2">
                                <Label for="w_defective_kernel"
                                    >Defective Kernel Weight</Label
                                >
                                <Input
                                    id="w_defective_kernel"
                                    v-model="form.w_defective_kernel"
                                    type="number"
                                    min="0"
                                    max="65535"
                                />
                                <p
                                    v-if="form.errors.w_defective_kernel"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.w_defective_kernel }}
                                </p>
                            </div>

                            <!-- Good Kernel Weight -->
                            <div class="space-y-2">
                                <Label for="w_good_kernel"
                                    >Good Kernel Weight</Label
                                >
                                <Input
                                    id="w_good_kernel"
                                    v-model="form.w_good_kernel"
                                    type="number"
                                    min="0"
                                    max="65535"
                                />
                                <p
                                    v-if="form.errors.w_good_kernel"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.w_good_kernel }}
                                </p>
                            </div>

                            <!-- Sample After Cut Weight -->
                            <div class="space-y-2">
                                <Label for="w_sample_after_cut"
                                    >Sample Weight After Cut</Label
                                >
                                <Input
                                    id="w_sample_after_cut"
                                    v-model="form.w_sample_after_cut"
                                    type="number"
                                    min="0"
                                    max="65535"
                                />
                                <p
                                    v-if="form.errors.w_sample_after_cut"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.w_sample_after_cut }}
                                </p>
                            </div>

                            <!-- Calculated Outturn Rate -->
                            <div class="space-y-2">
                                <Label>Outturn Rate (calculated)</Label>
                                <div
                                    class="rounded-md bg-muted/50 px-3 py-2 font-mono text-sm"
                                >
                                    {{
                                        calculatedOutturnRate
                                            ? `${calculatedOutturnRate} lbs/80kg`
                                            : 'Not calculated'
                                    }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Validation Alerts -->
                <div
                    v-if="
                        weightDifference > 5 ||
                        defectiveNutKernelDifference > 5 ||
                        goodKernelDifference > 10
                    "
                    class="space-y-2"
                >
                    <Alert v-if="weightDifference > 5" variant="destructive">
                        <AlertTriangle class="h-4 w-4" />
                        <AlertDescription>
                            Weight difference alert: Sample weight decreased by
                            {{ weightDifference.toFixed(1) }}g after cutting
                            (threshold: 5g)
                        </AlertDescription>
                    </Alert>

                    <Alert
                        v-if="defectiveNutKernelDifference > 5"
                        variant="destructive"
                    >
                        <AlertTriangle class="h-4 w-4" />
                        <AlertDescription>
                            Defective nut/kernel ratio alert: Difference of
                            {{ defectiveNutKernelDifference.toFixed(1) }}g
                            (threshold: 5g)
                        </AlertDescription>
                    </Alert>

                    <Alert
                        v-if="goodKernelDifference > 10"
                        variant="destructive"
                    >
                        <AlertTriangle class="h-4 w-4" />
                        <AlertDescription>
                            Good kernel weight alert: Difference of
                            {{ goodKernelDifference.toFixed(1) }}g (threshold:
                            10g)
                        </AlertDescription>
                    </Alert>
                </div>

                <!-- Notes -->
                <Card>
                    <CardHeader>
                        <CardTitle>Additional Notes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Label for="note">Notes</Label>
                            <Textarea
                                id="note"
                                v-model="form.note"
                                placeholder="Any additional observations or comments..."
                                rows="3"
                            />
                            <p
                                v-if="form.errors.note"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.note }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit Actions -->
                <div class="flex items-center justify-end gap-4">
                    <Button type="button" variant="outline" @click="cancel">
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2"
                    >
                        <Save class="h-4 w-4" />
                        {{
                            form.processing ? 'Saving...' : 'Save Cutting Test'
                        }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
