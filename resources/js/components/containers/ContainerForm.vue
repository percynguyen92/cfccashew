<script setup lang="ts">
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { Calculator, Save, X } from 'lucide-vue-next';
import { type Container, type Bill } from '@/types';
import * as containerRoutes from '@/routes/containers';

interface Props {
    container?: Container;
    bill?: Bill;
    billId?: number | string;
    isEditing?: boolean;
}

interface Emits {
    success: [];
    cancel: [];
}

const props = withDefaults(defineProps<Props>(), {
    isEditing: false,
});

const emit = defineEmits<Emits>();

const candidateBillId =
    props.container?.bill_id ??
    props.bill?.id ??
    (typeof props.billId === 'string' ? Number.parseInt(props.billId, 10) : props.billId);

const initialBillId =
    typeof candidateBillId === 'number' && Number.isFinite(candidateBillId)
        ? candidateBillId
        : null;

interface FormFields {
    bill_id: number | null;
    truck: string;
    container_number: string;
    quantity_of_bags: number | null;
    w_jute_bag: number | null;
    w_total: number | null;
    w_truck: number | null;
    w_container: number | null;
    w_dunnage_dribag: number | null;
    note: string;
}

const form = useForm<FormFields>({
    bill_id: initialBillId,
    truck: props.container?.truck ?? '',
    container_number: props.container?.container_number ?? '',
    quantity_of_bags: props.container?.quantity_of_bags ?? null,
    w_jute_bag: props.container?.w_jute_bag ?? 1.5,
    w_total: props.container?.w_total ?? null,
    w_truck: props.container?.w_truck ?? null,
    w_container: props.container?.w_container ?? null,
    w_dunnage_dribag: props.container?.w_dunnage_dribag ?? null,
    note: props.container?.note ?? '',
});

const asNumber = (value: number | null | undefined): number | null =>
    typeof value === 'number' && Number.isFinite(value) ? value : null;

const grossWeight = computed(() => {
    const total = asNumber(form.w_total);
    const truck = asNumber(form.w_truck);
    const container = asNumber(form.w_container);

    if (total === null || truck === null || container === null) {
        return null;
    }

    return Math.max(0, total - truck - container);
});

const tareWeight = computed(() => {
    const quantity = asNumber(form.quantity_of_bags);
    const juteBag = asNumber(form.w_jute_bag);

    if (quantity === null || juteBag === null) {
        return null;
    }

    return quantity * juteBag;
});

const netWeight = computed(() => {
    const gross = grossWeight.value;
    const tare = tareWeight.value;
    const dunnage = asNumber(form.w_dunnage_dribag);

    if (gross === null || tare === null || dunnage === null) {
        return null;
    }

    return Math.max(0, gross - dunnage - tare);
});

const calculationStatus = computed(() => ({
    gross: grossWeight.value !== null ? 'calculated' : 'pending',
    tare: tareWeight.value !== null ? 'calculated' : 'pending',
    net: netWeight.value !== null ? 'calculated' : 'pending',
}));

const calculationIssues = computed(() => {
    const issues: string[] = [];

    const total = asNumber(form.w_total);
    const truck = asNumber(form.w_truck);
    const container = asNumber(form.w_container);
    const dunnage = asNumber(form.w_dunnage_dribag);
    const gross = grossWeight.value;
    const tare = tareWeight.value;
    const net = netWeight.value;

    if (total !== null && truck !== null && container !== null) {
        if (total <= truck + container) {
            issues.push('Total weight should be greater than truck + container weight.');
        }
    }

    if (gross !== null && tare !== null && dunnage !== null) {
        if (gross <= dunnage + tare) {
            issues.push('Gross weight is insufficient for dunnage and tare weights.');
        }
    }

    if (net !== null && net <= 0) {
        issues.push('Net weight calculation results in zero or negative value. Please check your inputs.');
    }

    return {
        isValid: issues.length === 0,
        issues,
    };
});

const formatWeight = (weight: number | null): string => {
    if (weight === null) {
        return '-';
    }

    return weight.toLocaleString();
};

const formatDate = (date: string): string => new Date(date).toLocaleString();

const clearError = (field: keyof FormFields) => {
    if (form.errors[field]) {
        form.clearErrors(field);
    }
};

const validateForm = (): boolean => {
    form.clearErrors();
    let hasErrors = false;

    const billId = asNumber(form.bill_id);
    if (billId === null || !Number.isInteger(billId) || billId <= 0) {
        form.setError('bill_id', 'A valid bill is required.');
        hasErrors = true;
    } else {
        form.bill_id = billId;
    }

    const trimmedTruck = form.truck.trim();
    if (trimmedTruck.length > 20) {
        form.setError('truck', 'Truck identifier cannot exceed 20 characters.');
        hasErrors = true;
    }
    form.truck = trimmedTruck;

    const trimmedContainerNumber = form.container_number.trim().toUpperCase();
    if (trimmedContainerNumber.length > 0) {
        if (trimmedContainerNumber.length !== 11) {
            form.setError('container_number', 'Container number must be exactly 11 characters.');
            hasErrors = true;
        } else if (!/^[A-Z]{4}\d{7}$/.test(trimmedContainerNumber)) {
            form.setError(
                'container_number',
                'Container number must match the ISO format (4 letters + 7 digits).',
            );
            hasErrors = true;
        }
    }
    form.container_number = trimmedContainerNumber;

    const ensureInteger = (
        value: number | null,
        field: keyof FormFields,
        label: string,
        min: number,
    ): number | null => {
        if (value === null) {
            return null;
        }

        if (!Number.isInteger(value)) {
            form.setError(field, `${label} must be a whole number.`);
            hasErrors = true;

            return null;
        }

        if (value < min) {
            form.setError(field, `${label} cannot be less than ${min}.`);
            hasErrors = true;

            return null;
        }

        return value;
    };

    form.quantity_of_bags = ensureInteger(
        asNumber(form.quantity_of_bags),
        'quantity_of_bags',
        'Quantity of bags',
        0,
    );

    form.w_total = ensureInteger(asNumber(form.w_total), 'w_total', 'Total weight', 0);
    form.w_truck = ensureInteger(asNumber(form.w_truck), 'w_truck', 'Truck weight', 0);
    form.w_container = ensureInteger(
        asNumber(form.w_container),
        'w_container',
        'Container weight',
        0,
    );
    form.w_dunnage_dribag = ensureInteger(
        asNumber(form.w_dunnage_dribag),
        'w_dunnage_dribag',
        'Dunnage weight',
        0,
    );

    const juteWeight = asNumber(form.w_jute_bag);
    if (juteWeight !== null) {
        if (juteWeight < 0) {
            form.setError('w_jute_bag', 'Jute bag weight cannot be negative.');
            hasErrors = true;
        } else if (juteWeight > 99.99) {
            form.setError('w_jute_bag', 'Jute bag weight cannot exceed 99.99 kg.');
            hasErrors = true;
        } else {
            form.w_jute_bag = Number.parseFloat(juteWeight.toFixed(2));
        }
    }

    const trimmedNote = form.note.trim();
    if (trimmedNote.length > 65535) {
        form.setError('note', 'Note is too long.');
        hasErrors = true;
    }
    form.note = trimmedNote;

    return !hasErrors;
};

const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    form.transform((data) => ({
        bill_id: data.bill_id,
        truck: data.truck || null,
        container_number: data.container_number || null,
        quantity_of_bags: data.quantity_of_bags,
        w_jute_bag: data.w_jute_bag,
        w_total: data.w_total,
        w_truck: data.w_truck,
        w_container: data.w_container,
        w_dunnage_dribag: data.w_dunnage_dribag,
        note: data.note || null,
    }));

    if (props.isEditing && props.container) {
        form.put(containerRoutes.update.url(props.container.id.toString()), {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post(containerRoutes.store.url(), {
            onSuccess: () => emit('success'),
        });
    }
};

const handleCancel = () => emit('cancel');
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center justify-between">
                <span>Container Information</span>
                <div v-if="isEditing && container" class="space-y-1 text-sm text-muted-foreground">
                    <div>Created: {{ formatDate(container.created_at) }}</div>
                    <div>Updated: {{ formatDate(container.updated_at) }}</div>
                </div>
            </CardTitle>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <InputError
                    v-if="form.errors.bill_id"
                    :message="form.errors.bill_id"
                    class="text-red-600"
                />
                <!-- Container Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="truck">Truck</Label>
                        <Input
                            id="truck"
                            v-model="form.truck"
                            type="text"
                            placeholder="TRK-001"
                            :aria-invalid="Boolean(form.errors.truck)"
                            :class="{ 'border-red-500': form.errors.truck }"
                            @input="clearError('truck')"
                        />
                        <InputError :message="form.errors.truck" />
                    </div>
                    
                    <div class="space-y-2">
                        <Label for="container_number">Container Number</Label>
                        <Input
                            id="container_number"
                            v-model="form.container_number"
                            type="text"
                            placeholder="CONT1234567"
                            maxlength="11"
                            :aria-invalid="Boolean(form.errors.container_number)"
                            :class="{ 'border-red-500': form.errors.container_number }"
                            @input="clearError('container_number')"
                        />
                        <InputError :message="form.errors.container_number" />
                    </div>
                </div>
                
                <!-- Weight Inputs - Ordered according to design spec -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Weight Information</h3>
                    
                    <!-- Row 1: Quantity of Bags & Jute Bag Weight -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="quantity_of_bags">Quantity of Bags</Label>
                            <Input
                                id="quantity_of_bags"
                                v-model.number="form.quantity_of_bags"
                                type="number"
                                min="0"
                                placeholder="150"
                                :aria-invalid="Boolean(form.errors.quantity_of_bags)"
                                :class="{ 'border-red-500': form.errors.quantity_of_bags }"
                                @input="clearError('quantity_of_bags')"
                            />
                            <InputError :message="form.errors.quantity_of_bags" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="w_jute_bag">Jute Bag Weight (kg)</Label>
                            <Input
                                id="w_jute_bag"
                                v-model.number="form.w_jute_bag"
                                type="number"
                                step="0.01"
                                min="0"
                                max="99.99"
                                placeholder="1.50"
                                :aria-invalid="Boolean(form.errors.w_jute_bag)"
                                :class="{ 'border-red-500': form.errors.w_jute_bag }"
                                @input="clearError('w_jute_bag')"
                            />
                            <InputError :message="form.errors.w_jute_bag" />
                        </div>
                    </div>
                    
                    <!-- Row 2: Total, Truck, Container Weight -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="w_total">Total Weight (kg)</Label>
                            <Input
                                id="w_total"
                                v-model.number="form.w_total"
                                type="number"
                                min="1"
                                placeholder="25000"
                                :aria-invalid="Boolean(form.errors.w_total)"
                                :class="{ 'border-red-500': form.errors.w_total }"
                                @input="clearError('w_total')"
                            />
                            <InputError :message="form.errors.w_total" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="w_truck">Truck Weight (kg)</Label>
                            <Input
                                id="w_truck"
                                v-model.number="form.w_truck"
                                type="number"
                                min="1"
                                placeholder="10000"
                                :aria-invalid="Boolean(form.errors.w_truck)"
                                :class="{ 'border-red-500': form.errors.w_truck }"
                                @input="clearError('w_truck')"
                            />
                            <InputError :message="form.errors.w_truck" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="w_container">Container Weight (kg)</Label>
                            <Input
                                id="w_container"
                                v-model.number="form.w_container"
                                type="number"
                                min="1"
                                placeholder="2500"
                                :aria-invalid="Boolean(form.errors.w_container)"
                                :class="{ 'border-red-500': form.errors.w_container }"
                                @input="clearError('w_container')"
                            />
                            <InputError :message="form.errors.w_container" />
                        </div>
                    </div>
                    
                    <!-- Row 3: Dunnage Weight -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="w_dunnage_dribag">Dunnage Weight (kg)</Label>
                            <Input
                                id="w_dunnage_dribag"
                                v-model.number="form.w_dunnage_dribag"
                                type="number"
                                min="0"
                                placeholder="200"
                                :aria-invalid="Boolean(form.errors.w_dunnage_dribag)"
                                :class="{ 'border-red-500': form.errors.w_dunnage_dribag }"
                                @input="clearError('w_dunnage_dribag')"
                            />
                            <InputError :message="form.errors.w_dunnage_dribag" />
                        </div>
                    </div>
                </div>
                
                <!-- Calculated Weights Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium flex items-center gap-2">
                        <Calculator class="h-5 w-5" />
                        Calculated Weights
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Gross Weight -->
                        <div class="space-y-2">
                            <Label>Gross Weight (kg)</Label>
                            <div class="relative">
                                <Input
                                    :value="formatWeight(grossWeight)"
                                    readonly
                                    :class="[
                                        'bg-muted/50 cursor-not-allowed',
                                        calculationStatus.gross === 'calculated' ? 'border-green-300' : 'border-gray-300'
                                    ]"
                                />
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <Calculator class="h-4 w-4 text-muted-foreground" />
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Formula: Total - Truck - Container
                            </p>
                        </div>
                        
                        <!-- Tare Weight -->
                        <div class="space-y-2">
                            <Label>Tare Weight (kg)</Label>
                            <div class="relative">
                                <Input
                                    :value="formatWeight(tareWeight)"
                                    readonly
                                    :class="[
                                        'bg-muted/50 cursor-not-allowed',
                                        calculationStatus.tare === 'calculated' ? 'border-green-300' : 'border-gray-300'
                                    ]"
                                />
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <Calculator class="h-4 w-4 text-muted-foreground" />
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Formula: Quantity × Jute Bag Weight
                            </p>
                        </div>
                        
                        <!-- Net Weight -->
                        <div class="space-y-2">
                            <Label>Net Weight (kg)</Label>
                            <div class="relative">
                                <Input
                                    :value="formatWeight(netWeight)"
                                    readonly
                                    :class="[
                                        'bg-muted/50 cursor-not-allowed',
                                        calculationStatus.net === 'calculated' ? 'border-green-300' : 'border-gray-300'
                                    ]"
                                />
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <Calculator class="h-4 w-4 text-muted-foreground" />
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Formula: Gross - Dunnage - Tare
                            </p>
                        </div>
                    </div>
                    
                    <!-- Calculation Status Messages -->
                    <div
                        v-if="calculationStatus.gross === 'pending' || calculationStatus.tare === 'pending' || calculationStatus.net === 'pending'"
                        class="p-3 bg-blue-50 border border-blue-200 rounded-md"
                    >
                        <p class="text-sm text-blue-700">
                            <Calculator class="h-4 w-4 inline mr-1" />
                            Please fill in all required fields to see calculated weights
                        </p>
                    </div>
                    
                    <!-- Validation Errors -->
                    <div
                        v-if="!calculationIssues.isValid"
                        class="p-3 bg-red-50 border border-red-200 rounded-md"
                    >
                        <p class="text-sm font-medium text-red-700 mb-2">Calculation Issues:</p>
                        <ul class="text-sm text-red-600 space-y-1">
                            <li
                                v-for="issue in calculationIssues.issues"
                                :key="issue"
                                class="flex items-start gap-1"
                            >
                                <span class="font-bold">•</span>
                                <span>{{ issue }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Note -->
                <div class="space-y-2">
                    <Label for="note">Note</Label>
                    <Textarea
                        id="note"
                        v-model="form.note"
                        placeholder="Additional notes..."
                        rows="3"
                        :aria-invalid="Boolean(form.errors.note)"
                        :class="{ 'border-red-500': form.errors.note }"
                        @input="clearError('note')"
                    />
                    <InputError :message="form.errors.note" />
                </div>
                
                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <Button type="button" variant="outline" @click="handleCancel">
                        <X class="h-4 w-4 mr-2" />
                        Cancel
                    </Button>
                    <Button 
                        type="submit" 
                        :disabled="form.processing || !calculationIssues.isValid"
                        class="min-w-[120px]"
                    >
                        <Save class="h-4 w-4 mr-2" />
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
