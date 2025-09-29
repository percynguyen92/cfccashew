<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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

    if (hasErrors) {
        return false;
    }

    form.bill_number = trimmedBillNumber;
    form.seller = trimmedSeller;
    form.buyer = trimmedBuyer;
    form.note = trimmedNote;

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
const clearError = (field: 'bill_number' | 'seller' | 'buyer' | 'note') => {
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
