<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
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
import { useI18n } from 'vue-i18n';

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

const { t } = useI18n();

const normalizeType = (type: number | null | undefined): 1 | 2 | 3 => {
    if (type === 2 || type === 3) {
        return type;
    }

    return 1;
};

const initialType = props.cuttingTest
    ? normalizeType(props.cuttingTest.type)
    : normalizeType(props.defaultType);

interface FormFields {
    bill_id: number;
    container_id: number | null;
    type: number;
    moisture: string | number | null;
    sample_weight: number | string;
    nut_count: number | string | null;
    w_reject_nut: number | string | null;
    w_defective_nut: number | string | null;
    w_defective_kernel: number | string | null;
    w_good_kernel: number | string | null;
    w_sample_after_cut: number | string | null;
    note: string;
}

interface NormalizedPayload {
    bill_id: number;
    container_id: number | null;
    type: number;
    moisture: number | null;
    sample_weight: number;
    nut_count: number | null;
    w_reject_nut: number | null;
    w_defective_nut: number | null;
    w_defective_kernel: number | null;
    w_good_kernel: number | null;
    w_sample_after_cut: number | null;
    outturn_rate: number | null;
    note: string | null;
}

const form = useForm<FormFields>({
    bill_id: props.billId,
    container_id: props.cuttingTest?.container_id ?? null,
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

const typeOptions = computed(() => [
    { value: 1, label: t('cuttingTests.form.types.finalFirst') },
    { value: 2, label: t('cuttingTests.form.types.finalSecond') },
    { value: 3, label: t('cuttingTests.form.types.finalThird') },
]);

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
    isEditing.value
        ? t('cuttingTests.form.title.edit')
        : t('cuttingTests.form.title.create'),
);

const submitLabel = computed(() =>
    isEditing.value
        ? t('cuttingTests.form.actions.update')
        : t('cuttingTests.form.actions.create'),
);

const billLabel = computed(() => {
    if (!props.bill) {
        return t('cuttingTests.form.billLabel', { id: props.billId });
    }

    return props.bill.bill_number
        ? t('cuttingTests.form.billLabel', { id: props.bill.bill_number })
        : t('cuttingTests.form.billLabel', { id: props.bill.id });
});

const getTypeLabel = (type: number | string) => {
    const numeric = Number(type);
    return (
        typeOptions.value.find((option) => option.value === numeric)?.label ||
        t('cuttingTests.form.types.generic', { type: numeric })
    );
};

const toOptionalNumber = (value: unknown): number | null => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const numeric = Number(value);

    return Number.isFinite(numeric) ? numeric : null;
};

const clearError = (field: keyof FormFields) => {
    if (form.errors[field]) {
        form.clearErrors(field);
    }
};

const validateForm = (): NormalizedPayload | null => {
    form.clearErrors();
    let hasErrors = false;

    const normalized: NormalizedPayload = {
        bill_id: form.bill_id,
        container_id: null,
        type: form.type,
        moisture: null,
        sample_weight: 0,
        nut_count: null,
        w_reject_nut: null,
        w_defective_nut: null,
        w_defective_kernel: null,
        w_good_kernel: null,
        w_sample_after_cut: null,
        outturn_rate: null,
        note: null,
    };

    const billId = Number(form.bill_id);
    if (!Number.isInteger(billId) || billId <= 0) {
        form.setError('bill_id', t('cuttingTests.form.errors.billInvalid'));
        hasErrors = true;
    } else {
        normalized.bill_id = billId;
    }

    const type = Number(form.type);
    if (![1, 2, 3].includes(type)) {
        form.setError('type', t('cuttingTests.form.errors.typeInvalid'));
        hasErrors = true;
    } else {
        normalized.type = type;
    }

    if (form.container_id !== null && form.container_id !== undefined) {
        form.setError('type', t('cuttingTests.form.errors.containerNotAllowed'));
        hasErrors = true;
    }

    normalized.container_id = null;

    const sampleWeight = toOptionalNumber(form.sample_weight);
    if (sampleWeight === null || !Number.isInteger(sampleWeight)) {
        form.setError(
            'sample_weight',
            t('cuttingTests.form.errors.integer', {
                label: t('cuttingTests.form.fields.sampleWeight.label'),
            }),
        );
        hasErrors = true;
    } else if (sampleWeight < 1 || sampleWeight > 65535) {
        form.setError(
            'sample_weight',
            t('cuttingTests.form.errors.range', {
                label: t('cuttingTests.form.fields.sampleWeight.label'),
                min: 1,
                max: 65535,
                unit: t('cuttingTests.form.units.grams'),
            }),
        );
        hasErrors = true;
    } else {
        normalized.sample_weight = sampleWeight;
    }

    const moisture = toOptionalNumber(form.moisture);
    if (moisture !== null) {
        if (moisture < 0 || moisture > 100) {
            form.setError(
                'moisture',
                t('cuttingTests.form.errors.moistureRange'),
            );
            hasErrors = true;
        } else {
            normalized.moisture = Number.parseFloat(moisture.toFixed(2));
        }
    }

    const ensureOptionalInteger = (
        rawValue: unknown,
        field: keyof FormFields,
        label: string,
        max = 65535,
        unit?: string,
    ): number | null => {
        const value = toOptionalNumber(rawValue);

        if (value === null) {
            return null;
        }

        if (!Number.isInteger(value)) {
            form.setError(
                field,
                t('cuttingTests.form.errors.integer', {
                    label,
                }),
            );
            hasErrors = true;

            return null;
        }

        if (value < 0 || value > max) {
            form.setError(
                field,
                t('cuttingTests.form.errors.range', {
                    label,
                    min: 0,
                    max,
                    unit,
                }),
            );
            hasErrors = true;

            return null;
        }

        return value;
    };

    normalized.nut_count = ensureOptionalInteger(
        form.nut_count,
        'nut_count',
        t('cuttingTests.form.fields.nutCount.label'),
        65535,
    );
    normalized.w_reject_nut = ensureOptionalInteger(
        form.w_reject_nut,
        'w_reject_nut',
        t('cuttingTests.form.fields.rejectNutWeight.label'),
        65535,
        t('cuttingTests.form.units.grams'),
    );
    normalized.w_defective_nut = ensureOptionalInteger(
        form.w_defective_nut,
        'w_defective_nut',
        t('cuttingTests.form.fields.defectiveNutWeight.label'),
        65535,
        t('cuttingTests.form.units.grams'),
    );
    normalized.w_defective_kernel = ensureOptionalInteger(
        form.w_defective_kernel,
        'w_defective_kernel',
        t('cuttingTests.form.fields.defectiveKernelWeight.label'),
        65535,
        t('cuttingTests.form.units.grams'),
    );
    normalized.w_good_kernel = ensureOptionalInteger(
        form.w_good_kernel,
        'w_good_kernel',
        t('cuttingTests.form.fields.goodKernelWeight.label'),
        65535,
        t('cuttingTests.form.units.grams'),
    );
    normalized.w_sample_after_cut = ensureOptionalInteger(
        form.w_sample_after_cut,
        'w_sample_after_cut',
        t('cuttingTests.form.fields.sampleAfterCut.label'),
        65535,
        t('cuttingTests.form.units.grams'),
    );

    const trimmedNote = form.note.trim();
    if (trimmedNote.length > 65535) {
        form.setError('note', t('cuttingTests.form.errors.noteTooLong'));
        hasErrors = true;
    }
    form.note = trimmedNote;
    normalized.note = trimmedNote === '' ? null : trimmedNote;

    if (hasErrors) {
        return null;
    }

    const defectiveKernel = normalized.w_defective_kernel ?? 0;
    const goodKernel = normalized.w_good_kernel ?? 0;
    if (defectiveKernel > 0 || goodKernel > 0) {
        normalized.outturn_rate = Number(
            (((defectiveKernel / 2 + goodKernel) * 80) / 453.6).toFixed(2),
        );
    }

    return normalized;
};

watch(
    () => form.type,
    (type) => {
        if (![1, 2, 3].includes(type)) {
            form.type = 1;

            return;
        }

        form.container_id = null;
    },
);

const handleSubmit = () => {
    const payload = validateForm();

    if (!payload) {
        return;
    }

    form.transform(() => payload);

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
                    <CardTitle>
                        {{ t('cuttingTests.form.sections.details.title') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <InputError
                        v-if="form.errors.bill_id"
                        :message="form.errors.bill_id"
                    />

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="type">
                                {{ t('cuttingTests.form.fields.type.label') }}
                            </Label>
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
                            <Label for="sample_weight">
                                {{ t('cuttingTests.form.fields.sampleWeight.label') }}
                            </Label>
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
                            <Label for="moisture">
                                {{ t('cuttingTests.form.fields.moisture.label') }}
                            </Label>
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
                            <Label for="nut_count">
                                {{ t('cuttingTests.form.fields.nutCount.label') }}
                            </Label>
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
                    <CardTitle>
                        {{ t('cuttingTests.form.sections.weights.title') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="w_reject_nut">
                                {{ t('cuttingTests.form.fields.rejectNutWeight.label') }}
                            </Label>
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
                            <Label for="w_defective_nut">
                                {{ t('cuttingTests.form.fields.defectiveNutWeight.label') }}
                            </Label>
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
                            <Label for="w_defective_kernel">
                                {{ t('cuttingTests.form.fields.defectiveKernelWeight.label') }}
                            </Label>
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
                            <Label for="w_good_kernel">
                                {{ t('cuttingTests.form.fields.goodKernelWeight.label') }}
                            </Label>
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
                            <Label for="w_sample_after_cut">
                                {{ t('cuttingTests.form.fields.sampleAfterCut.label') }}
                            </Label>
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
                        <Label>
                            {{ t('cuttingTests.form.sections.weights.outturnCalculated') }}
                        </Label>
                        <div class="rounded-md bg-muted px-3 py-2 font-mono text-sm">
                            {{
                                calculatedOutturnRate
                                    ? t('cuttingTests.form.units.outturnRate', {
                                        value: calculatedOutturnRate,
                                    })
                                    : t('cuttingTests.form.status.notCalculated')
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
                        {{
                            t('cuttingTests.form.alerts.weight', {
                                difference: weightDifference.toFixed(1),
                            })
                        }}
                    </span>
                </div>

                <div
                    v-if="defectiveNutKernelDifference > 5"
                    class="flex items-start gap-2 rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        {{
                            t('cuttingTests.form.alerts.defectiveRatio', {
                                difference: defectiveNutKernelDifference.toFixed(1),
                            })
                        }}
                    </span>
                </div>

                <div
                    v-if="goodKernelDifference > 10"
                    class="flex items-start gap-2 rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4" />
                    <span>
                        {{
                            t('cuttingTests.form.alerts.goodKernel', {
                                difference: goodKernelDifference.toFixed(1),
                            })
                        }}
                    </span>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>
                        {{ t('cuttingTests.form.sections.notes.title') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <Label for="note">
                            {{ t('cuttingTests.form.fields.note.label') }}
                        </Label>
                        <Textarea
                            id="note"
                            v-model="form.note"
                            rows="3"
                            :placeholder="t('cuttingTests.form.fields.note.placeholder')"
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
                    {{ t('common.actions.cancel') }}
                </Button>
                <Button type="submit" :disabled="isSubmitting">
                    <span v-if="isSubmitting">
                        {{
                            isEditing
                                ? t('common.states.updating')
                                : t('common.states.creating')
                        }}
                    </span>
                    <span v-else>
                        {{ submitLabel }}
                    </span>
                </Button>
            </div>
        </form>
    </div>
</template>
