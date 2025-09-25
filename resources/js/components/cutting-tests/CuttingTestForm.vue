<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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
import * as cuttingTestRoutes from '@/routes/cutting-tests';
import type { Bill, CuttingTest } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { computed, watch } from 'vue';

interface Props {
    billId: number;
    bill?: Bill;
    cuttingTest?: CuttingTest | null;
    defaultType?: 1 | 2 | 3;
}

const props = withDefaults(defineProps<Props>(), {
    bill: undefined,
    cuttingTest: null,
    defaultType: 1,
});

const emit = defineEmits<{
    success: [];
    cancel: [];
}>();

const normalizeType = (type: number | null | undefined): 1 | 2 | 3 => {
    if (type === 2 || type === 3) {
        return type;
    }
    return 1;
};

const initialType = props.cuttingTest
    ? normalizeType(props.cuttingTest.type)
    : normalizeType(props.defaultType);

const form = useForm({
    bill_id: props.billId,
    container_id: null as number | null,
    type: initialType,
    moisture: props.cuttingTest?.moisture ?? '',
    sample_weight: props.cuttingTest?.sample_weight ?? 1000,
    nut_count: props.cuttingTest?.nut_count ?? '',
    w_reject_nut: props.cuttingTest?.w_reject_nut ?? '',
    w_defective_nut: props.cuttingTest?.w_defective_nut ?? '',
    w_defective_kernel: props.cuttingTest?.w_defective_kernel ?? '',
    w_good_kernel: props.cuttingTest?.w_good_kernel ?? '',
    w_sample_after_cut: props.cuttingTest?.w_sample_after_cut ?? '',
    note: props.cuttingTest?.note ?? '',
});

const isEditing = computed(() => Boolean(props.cuttingTest));
const isSubmitting = computed(() => form.processing);

const typeOptions = [
    { value: 1, label: 'Final Sample 1st Cut' },
    { value: 2, label: 'Final Sample 2nd Cut' },
    { value: 3, label: 'Final Sample 3rd Cut' },
] as const;

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

const formTitle = computed(() =>
    isEditing.value ? 'Edit Final Sample' : 'Add Final Sample',
);

const submitLabel = computed(() =>
    isEditing.value ? 'Update Cutting Test' : 'Create Cutting Test',
);

const billLabel = computed(() => {
    if (!props.bill) {
        return `Bill #${props.billId}`;
    }

    return props.bill.bill_number
        ? `Bill #${props.bill.bill_number}`
        : `Bill #${props.bill.id}`;
});

const getTypeLabel = (type: number | string) => {
    const numeric = Number(type);
    return (
        typeOptions.find((option) => option.value === numeric)?.label ||
        `Type ${numeric}`
    );
};

const clearError = (
    field:
        | 'moisture'
        | 'nut_count'
        | 'w_reject_nut'
        | 'w_defective_nut'
        | 'w_defective_kernel'
        | 'w_good_kernel'
        | 'w_sample_after_cut'
        | 'note'
        | 'sample_weight'
        | 'type',
) => {
    if (form.errors[field]) {
        form.clearErrors(field);
    }
};

watch(
    () => form.type,
    (type) => {
        if (![1, 2, 3].includes(type)) {
            form.type = 1;
        }

        form.container_id = null;
    },
);

const handleSubmit = () => {
    if (isEditing.value && props.cuttingTest) {
        form.put(cuttingTestRoutes.update.url(props.cuttingTest.id), {
            onSuccess: () => {
                emit('success');
            },
        });
    } else {
        form.post(cuttingTestRoutes.store.url(), {
            onSuccess: () => {
                emit('success');
            },
        });
    }
};

const handleCancel = () => {
    emit('cancel');
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-2">
            <h2 class="text-xl font-semibold">{{ formTitle }}</h2>
            <p class="text-sm text-muted-foreground">{{ billLabel }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Test Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="type">Test Type *</Label>
                            <Select
                                :model-value="form.type"
                                @update:model-value="(value) => {
                                    form.type = Number(value);
                                    clearError('type');
                                }"
                            >
                                <SelectTrigger>
                                    <SelectValue
                                        :placeholder="getTypeLabel(form.type)"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in typeOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.type" />
                        </div>

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
                                @input="clearError('sample_weight')"
                            />
                            <InputError :message="form.errors.sample_weight" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="moisture">Moisture (%)</Label>
                            <Input
                                id="moisture"
                                v-model="form.moisture"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                @input="clearError('moisture')"
                            />
                            <InputError :message="form.errors.moisture" />
                        </div>

                        <div class="space-y-2">
                            <Label for="nut_count">Nut Count</Label>
                            <Input
                                id="nut_count"
                                v-model="form.nut_count"
                                type="number"
                                min="0"
                                max="65535"
                                @input="clearError('nut_count')"
                            />
                            <InputError :message="form.errors.nut_count" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Weight Measurements (grams)</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="w_reject_nut">Reject Nut Weight</Label>
                            <Input
                                id="w_reject_nut"
                                v-model="form.w_reject_nut"
                                type="number"
                                min="0"
                                max="65535"
                                @input="clearError('w_reject_nut')"
                            />
                            <InputError
                                :message="form.errors.w_reject_nut"
                            />
                        </div>

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
                                @input="clearError('w_defective_nut')"
                            />
                            <InputError
                                :message="form.errors.w_defective_nut"
                            />
                        </div>

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
                                @input="clearError('w_defective_kernel')"
                            />
                            <InputError
                                :message="form.errors.w_defective_kernel"
                            />
                        </div>

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
                                @input="clearError('w_good_kernel')"
                            />
                            <InputError :message="form.errors.w_good_kernel" />
                        </div>

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
                                @input="clearError('w_sample_after_cut')"
                            />
                            <InputError
                                :message="form.errors.w_sample_after_cut"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Calculated Outturn Rate</Label>
                        <div class="rounded-md bg-muted px-3 py-2 font-mono text-sm">
                            {{
                                calculatedOutturnRate
                                    ? `${calculatedOutturnRate} lbs/80kg`
                                    : 'Not calculated'
                            }}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div
                v-if="
                    weightDifference > 5 ||
                    defectiveNutKernelDifference > 5 ||
                    goodKernelDifference > 10
                "
                class="space-y-2"
            >
                <div
                    v-if="weightDifference > 5"
                    class="flex items-start gap-2 rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        Weight difference alert: Sample weight decreased by
                        {{ weightDifference.toFixed(1) }}g after cutting
                        (threshold: 5g)
                    </span>
                </div>

                <div
                    v-if="defectiveNutKernelDifference > 5"
                    class="flex items-start gap-2 rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        Defective nut/kernel ratio alert: Difference of
                        {{ defectiveNutKernelDifference.toFixed(1) }}g
                        (threshold: 5g)
                    </span>
                </div>

                <div
                    v-if="goodKernelDifference > 10"
                    class="flex items-start gap-2 rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        Good kernel weight alert: Difference of
                        {{ goodKernelDifference.toFixed(1) }}g (threshold: 10g)
                    </span>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <Label for="note">Observation Notes</Label>
                        <Textarea
                            id="note"
                            v-model="form.note"
                            rows="3"
                            placeholder="Additional observations or comments"
                            @input="clearError('note')"
                        />
                        <InputError :message="form.errors.note" />
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-3">
                <Button
                    type="button"
                    variant="outline"
                    @click="handleCancel"
                    :disabled="isSubmitting"
                >
                    Cancel
                </Button>
                <Button type="submit" :disabled="isSubmitting">
                    <span v-if="isSubmitting">
                        {{ isEditing ? 'Saving...' : 'Creating...' }}
                    </span>
                    <span v-else>
                        {{ submitLabel }}
                    </span>
                </Button>
            </div>
        </form>
    </div>
</template>
