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

// Computed properties
const isSubmitting = computed(() => form.processing);
const formTitle = computed(() =>
    props.isEditing ? 'Edit Bill' : 'Create New Bill',
);
const submitButtonText = computed(() =>
    props.isEditing ? 'Update Bill' : 'Create Bill',
);

const validateForm = (): boolean => {
    form.clearErrors();

    const trimmedBillNumber = form.bill_number.trim();
    const trimmedSeller = form.seller.trim();
    const trimmedBuyer = form.buyer.trim();
    const trimmedNote = form.note.trim();

    let hasErrors = false;

    if (!trimmedBillNumber) {
        form.setError('bill_number', 'Bill number is required.');
        hasErrors = true;
    }

    if (!trimmedSeller) {
        form.setError('seller', 'Seller is required.');
        hasErrors = true;
    }

    if (!trimmedBuyer) {
        form.setError('buyer', 'Buyer is required.');
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
                        <Label for="bill_number">Bill Number</Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.bill_number"
                        />
                    </div>
                    <Input
                        id="bill_number"
                        v-model="form.bill_number"
                        type="text"
                        placeholder="Enter bill number (required)"
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
                        <Label for="seller">Seller</Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.seller"
                        />
                    </div>
                    <Input
                        id="seller"
                        v-model="form.seller"
                        type="text"
                        placeholder="Enter seller name (required)"
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
                        <Label for="buyer">Buyer</Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.buyer"
                        />
                    </div>
                    <Input
                        id="buyer"
                        v-model="form.buyer"
                        type="text"
                        placeholder="Enter buyer name (required)"
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
                        <Label for="note">Note</Label>
                        <InputError
                            class="text-right"
                            :message="form.errors.note"
                        />
                    </div>
                    <Textarea
                        id="note"
                        v-model="form.note"
                        placeholder="Enter additional notes (optional)"
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
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="isSubmitting">
                        <span v-if="isSubmitting">
                            {{ isEditing ? 'Updating...' : 'Creating...' }}
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
