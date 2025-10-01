<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import * as bills from '@/routes/bills';
import type { Bill } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    bill?: Bill;
    isEditing?: boolean;
    redirectUrl?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    isEditing: false,
    redirectUrl: null,
});

const emit = defineEmits<{
    success: [];
    cancel: [];
}>();

// Form data
const form = useForm({
    bill_number: props.bill?.bill_number || '',
    seller: props.bill?.seller || '',
    buyer: props.bill?.buyer || '',
    note: props.bill?.note || '',
    w_dunnage_dribag: props.bill?.w_dunnage_dribag ?? null,
    w_jute_bag: props.bill?.w_jute_bag ?? 1.00,
    net_on_bl: props.bill?.net_on_bl ?? null,
    quantity_of_bags_on_bl: props.bill?.quantity_of_bags_on_bl ?? null,
    origin: props.bill?.origin ?? '',
    inspection_start_date: props.bill?.inspection_start_date ?? '',
    inspection_end_date: props.bill?.inspection_end_date ?? '',
    inspection_location: props.bill?.inspection_location ?? '',
    sampling_ratio: props.bill?.sampling_ratio ?? 10.00,
    redirect_url: props.redirectUrl,
});

const page = usePage();
const { t } = useI18n();

// Computed properties
const isSubmitting = computed(() => form.processing);
const formTitle = computed(() =>
    props.isEditing
        ? t('bills.form.title.edit')
        : t('bills.form.title.create'),
);
const submitButtonText = computed(() =>
    props.isEditing
        ? t('bills.form.submit.update')
        : t('bills.form.submit.create'),
);
const submittingText = computed(() =>
    props.isEditing
        ? t('bills.form.submit.updating')
        : t('bills.form.submit.creating'),
);

const validateForm = (): boolean => {
    form.clearErrors();

    const trimmedBillNumber = form.bill_number.trim();
    const trimmedSeller = form.seller.trim();
    const trimmedBuyer = form.buyer.trim();
    const trimmedNote = form.note.trim();
    const trimmedOrigin = form.origin.trim();
    const trimmedLocation = form.inspection_location.trim();

    let hasErrors = false;

    if (!trimmedBillNumber) {
        form.setError(
            'bill_number',
            t('validation.custom.bill_number.required'),
        );
        hasErrors = true;
    }

    if (!trimmedSeller) {
        form.setError('seller', t('validation.custom.seller.required'));
        hasErrors = true;
    }

    if (!trimmedBuyer) {
        form.setError('buyer', t('validation.custom.buyer.required'));
        hasErrors = true;
    }

    // Weights validation
    const juteBag = Number(form.w_jute_bag);
    if (juteBag < 0 || juteBag > 99.99) {
        form.setError('w_jute_bag', t('validation.custom.w_jute_bag.max'));
        hasErrors = true;
    }

    const dunnage = Number(form.w_dunnage_dribag);
    if (dunnage !== null && dunnage < 0) {
        form.setError('w_dunnage_dribag', t('validation.custom.weights.min'));
        hasErrors = true;
    }

    const netBl = Number(form.net_on_bl);
    if (netBl !== null && netBl < 0) {
        form.setError('net_on_bl', t('validation.custom.weights.min'));
        hasErrors = true;
    }

    const quantityBl = Number(form.quantity_of_bags_on_bl);
    if (quantityBl !== null && quantityBl < 0) {
        form.setError('quantity_of_bags_on_bl', t('validation.custom.weights.min'));
        hasErrors = true;
    }

    const sampling = Number(form.sampling_ratio);
    if (sampling < 0 || sampling > 100) {
        form.setError('sampling_ratio', t('validation.custom.sampling_ratio.max'));
        hasErrors = true;
    }

    if (hasErrors) {
        return false;
    }

    form.bill_number = trimmedBillNumber;
    form.seller = trimmedSeller;
    form.buyer = trimmedBuyer;
    form.note = trimmedNote;
    form.origin = trimmedOrigin;
    form.inspection_location = trimmedLocation;

    return true;
};

// Methods
const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    if (!props.isEditing) {
        form.redirect_url = props.redirectUrl ?? page.url ?? null;
    }

    if (props.isEditing && props.bill) {
        form.put(bills.update.url(props.bill.id), {
            onSuccess: () => {
                emit('success');
            },
        });
    } else {
        form.post(bills.store.url(), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                emit('success');
                form.reset('bill_number', 'seller', 'buyer', 'note');
            },
        });
    }
};

const handleCancel = () => {
    emit('cancel');
};

// Clear form errors when input changes
const clearError = (field: 'bill_number' | 'seller' | 'buyer' | 'note' | 'w_dunnage_dribag' | 'w_jute_bag' | 'net_on_bl' | 'quantity_of_bags_on_bl' | 'origin' | 'inspection_start_date' | 'inspection_end_date' | 'inspection_location' | 'sampling_ratio') => {
    if (form.errors[field]) {
        form.clearErrors(field);
    }
};
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle>{{ formTitle }}</CardTitle>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Bill Number -->
                <div class="space-y-2">
                    <div
                        class="flex flex-wrap items-center justify-start gap-4"
                    >
                        <Label for="bill_number">
                            {{ t('bills.form.fields.billNumber.label') }}
                        </Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.bill_number"
                        />
                    </div>
                    <Input
                        id="bill_number"
                        v-model="form.bill_number"
                        type="text"
                        :placeholder="
                            t('bills.form.fields.billNumber.placeholder')
                        "
                        maxlength="20"
                        :aria-invalid="!!form.errors.bill_number"
                        @input="clearError('bill_number')"
                    />
                </div>

                <!-- Seller -->
                <div class="space-y-2">
                    <div
                        class="flex flex-wrap items-center justify-start gap-4"
                    >
                        <Label for="seller">
                            {{ t('bills.form.fields.seller.label') }}
                        </Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.seller"
                        />
                    </div>
                    <Input
                        id="seller"
                        v-model="form.seller"
                        type="text"
                        :placeholder="
                            t('bills.form.fields.seller.placeholder')
                        "
                        maxlength="255"
                        :aria-invalid="!!form.errors.seller"
                        @input="clearError('seller')"
                    />
                </div>

                <!-- Buyer -->
                <div class="space-y-2">
                    <div
                        class="flex flex-wrap items-center justify-start gap-4"
                    >
                        <Label for="buyer">
                            {{ t('bills.form.fields.buyer.label') }}
                        </Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.buyer"
                        />
                    </div>
                    <Input
                        id="buyer"
                        v-model="form.buyer"
                        type="text"
                        :placeholder="
                            t('bills.form.fields.buyer.placeholder')
                        "
                        maxlength="255"
                        :aria-invalid="!!form.errors.buyer"
                        @input="clearError('buyer')"
                    />
                </div>

                <!-- Weights Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">{{ t('bills.form.sections.weights') || 'Weights' }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="w_jute_bag">
                                    {{ t('bills.form.fields.w_jute_bag.label') || 'Jute Bag Weight (kg)' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.w_jute_bag" />
                            </div>
                            <Input
                                id="w_jute_bag"
                                v-model.number="form.w_jute_bag"
                                type="number"
                                step="0.01"
                                min="0"
                                max="99.99"
                                :placeholder="t('bills.form.fields.w_jute_bag.placeholder') || '1.00'"
                                :aria-invalid="!!form.errors.w_jute_bag"
                                @input="clearError('w_jute_bag')"
                            />
                        </div>
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="w_dunnage_dribag">
                                    {{ t('bills.form.fields.w_dunnage_dribag.label') || 'Dunnage/Dri Bag Weight (kg)' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.w_dunnage_dribag" />
                            </div>
                            <Input
                                id="w_dunnage_dribag"
                                v-model.number="form.w_dunnage_dribag"
                                type="number"
                                min="0"
                                :placeholder="t('bills.form.fields.w_dunnage_dribag.placeholder') || '0'"
                                :aria-invalid="!!form.errors.w_dunnage_dribag"
                                @input="clearError('w_dunnage_dribag')"
                            />
                        </div>
                    </div>
                </div>

                <!-- BL Information Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">{{ t('bills.form.sections.blInfo') || 'BL Information' }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="net_on_bl">
                                    {{ t('bills.form.fields.net_on_bl.label') || 'Net on BL (kg)' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.net_on_bl" />
                            </div>
                            <Input
                                id="net_on_bl"
                                v-model.number="form.net_on_bl"
                                type="number"
                                min="0"
                                :placeholder="t('bills.form.fields.net_on_bl.placeholder') || '0'"
                                :aria-invalid="!!form.errors.net_on_bl"
                                @input="clearError('net_on_bl')"
                            />
                        </div>
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="quantity_of_bags_on_bl">
                                    {{ t('bills.form.fields.quantity_of_bags_on_bl.label') || 'Quantity of Bags on BL' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.quantity_of_bags_on_bl" />
                            </div>
                            <Input
                                id="quantity_of_bags_on_bl"
                                v-model.number="form.quantity_of_bags_on_bl"
                                type="number"
                                min="0"
                                :placeholder="t('bills.form.fields.quantity_of_bags_on_bl.placeholder') || '0'"
                                :aria-invalid="!!form.errors.quantity_of_bags_on_bl"
                                @input="clearError('quantity_of_bags_on_bl')"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center justify-start gap-4">
                            <Label for="origin">
                                {{ t('bills.form.fields.origin.label') || 'Origin' }}
                            </Label>
                            <InputError class="text-right" :message="form.errors.origin" />
                        </div>
                        <Input
                            id="origin"
                            v-model="form.origin"
                            type="text"
                            maxlength="255"
                            :placeholder="t('bills.form.fields.origin.placeholder') || 'Enter origin'"
                            :aria-invalid="!!form.errors.origin"
                            @input="clearError('origin')"
                        />
                    </div>
                </div>

                <!-- Inspection Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">{{ t('bills.form.sections.inspection') || 'Inspection Data' }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="inspection_start_date">
                                    {{ t('bills.form.fields.inspection_start_date.label') || 'Inspection Start Date' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.inspection_start_date" />
                            </div>
                            <Input
                                id="inspection_start_date"
                                v-model="form.inspection_start_date"
                                type="date"
                                :placeholder="t('bills.form.fields.inspection_start_date.placeholder') || 'YYYY-MM-DD'"
                                :aria-invalid="!!form.errors.inspection_start_date"
                                @input="clearError('inspection_start_date')"
                            />
                        </div>
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="inspection_end_date">
                                    {{ t('bills.form.fields.inspection_end_date.label') || 'Inspection End Date' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.inspection_end_date" />
                            </div>
                            <Input
                                id="inspection_end_date"
                                v-model="form.inspection_end_date"
                                type="date"
                                :placeholder="t('bills.form.fields.inspection_end_date.placeholder') || 'YYYY-MM-DD'"
                                :aria-invalid="!!form.errors.inspection_end_date"
                                @input="clearError('inspection_end_date')"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="inspection_location">
                                    {{ t('bills.form.fields.inspection_location.label') || 'Inspection Location' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.inspection_location" />
                            </div>
                            <Input
                                id="inspection_location"
                                v-model="form.inspection_location"
                                type="text"
                                maxlength="255"
                                :placeholder="t('bills.form.fields.inspection_location.placeholder') || 'Enter location'"
                                :aria-invalid="!!form.errors.inspection_location"
                                @input="clearError('inspection_location')"
                            />
                        </div>
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center justify-start gap-4">
                                <Label for="sampling_ratio">
                                    {{ t('bills.form.fields.sampling_ratio.label') || 'Sampling Ratio (%)' }}
                                </Label>
                                <InputError class="text-right" :message="form.errors.sampling_ratio" />
                            </div>
                            <Input
                                id="sampling_ratio"
                                v-model.number="form.sampling_ratio"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                :placeholder="t('bills.form.fields.sampling_ratio.placeholder') || '10.00'"
                                :aria-invalid="!!form.errors.sampling_ratio"
                                @input="clearError('sampling_ratio')"
                            />
                        </div>
                    </div>
                </div>

                <!-- Note -->
                <div class="space-y-2">
                    <div
                        class="flex flex-wrap items-center justify-start gap-4"
                    >
                        <Label for="note">
                            {{ t('bills.form.fields.note.label') }}
                        </Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.note"
                        />
                    </div>
                    <Textarea
                        id="note"
                        v-model="form.note"
                        :placeholder="
                            t('bills.form.fields.note.placeholder')
                        "
                        rows="4"
                        :aria-invalid="!!form.errors.note"
                        @input="clearError('note')"
                    />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-4">
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
                            {{ submittingText }}
                        </span>
                        <span v-else>
                            {{ submitButtonText }}
                        </span>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
