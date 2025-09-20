<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
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

// Form data interface
interface FormData {
    // Input fields (editable)
    truck: string | null;
    container_number: string | null;
    quantity_of_bags: number | null;
    w_jute_bag: number;
    w_total: number | null;
    w_truck: number | null;
    w_container: number | null;
    w_dunnage_dribag: number | null;
    note: string;
    bill_id: number;
    
    // Calculated fields (read-only) - these will be computed
    w_gross?: number | null;
    w_tare?: number | null;
    w_net?: number | null;
}

// Initialize form data
const form = reactive<FormData>({
    truck: props.container?.truck || null,
    container_number: props.container?.container_number || null,
    quantity_of_bags: props.container?.quantity_of_bags || null,
    w_jute_bag: props.container?.w_jute_bag || 1.5,
    w_total: props.container?.w_total || null,
    w_truck: props.container?.w_truck || null,
    w_container: props.container?.w_container || null,
    w_dunnage_dribag: props.container?.w_dunnage_dribag || null,
    note: props.container?.note || '',
    bill_id: props.container?.bill_id || 
             (props.bill?.id) || 
             (typeof props.billId === 'string' ? parseInt(props.billId) : props.billId) || 
             0,
});

// Error tracking
const errors = reactive<Record<string, string>>({});
const processing = ref(false);

// Computed properties for automatic calculations
const grossWeight = computed(() => {
    const { w_total, w_truck, w_container } = form;
    if (w_total === null || w_truck === null || w_container === null) return null;
    return Math.max(0, w_total - w_truck - w_container);
});

const tareWeight = computed(() => {
    const { quantity_of_bags, w_jute_bag } = form;
    if (quantity_of_bags === null || w_jute_bag === null) return null;
    return quantity_of_bags * w_jute_bag;
});

const netWeight = computed(() => {
    const gross = grossWeight.value;
    const tare = tareWeight.value;
    const { w_dunnage_dribag } = form;
    
    if (gross === null || tare === null || w_dunnage_dribag === null) return null;
    return Math.max(0, gross - w_dunnage_dribag - tare);
});

// Calculation status for UI feedback
const calculationStatus = computed(() => ({
    gross: grossWeight.value !== null ? 'calculated' : 'pending',
    tare: tareWeight.value !== null ? 'calculated' : 'pending',
    net: netWeight.value !== null ? 'calculated' : 'pending',
}));

// Validation for business logic constraints
const validationRules = computed(() => {
    const issues = [];
    
    // Total weight should be greater than truck + container weight
    if (form.w_total !== null && form.w_truck !== null && form.w_container !== null) {
        if (form.w_total <= form.w_truck + form.w_container) {
            issues.push('Total weight should be greater than truck + container weight');
        }
    }
    
    // Net weight should be positive
    if (netWeight.value !== null && netWeight.value <= 0) {
        issues.push('Net weight calculation results in zero or negative value. Please check your inputs.');
    }
    
    // Gross weight should be sufficient for dunnage + tare
    const gross = grossWeight.value;
    const tare = tareWeight.value;
    if (gross !== null && tare !== null && form.w_dunnage_dribag !== null) {
        if (gross <= form.w_dunnage_dribag + tare) {
            issues.push('Gross weight is insufficient for dunnage and tare weights');
        }
    }
    
    return {
        isValid: issues.length === 0,
        issues
    };
});

// Format number display
const formatWeight = (weight: number | null): string => {
    if (weight === null) return '-';
    return weight.toLocaleString();
};

// Format date display
const formatDate = (date: string): string => {
    return new Date(date).toLocaleString();
};

// Submit form
const submit = () => {
    if (!validationRules.value.isValid) {
        return;
    }
    
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);
    
    // Prepare data for submission (exclude calculated fields)
    const submitData = {
        truck: form.truck,
        container_number: form.container_number,
        quantity_of_bags: form.quantity_of_bags,
        w_jute_bag: form.w_jute_bag,
        w_total: form.w_total,
        w_truck: form.w_truck,
        w_container: form.w_container,
        w_dunnage_dribag: form.w_dunnage_dribag,
        note: form.note || null, // Convert empty string back to null for submission
        bill_id: form.bill_id,
    };
    
    const url = props.isEditing 
        ? containerRoutes.update.url(props.container!.id.toString())
        : containerRoutes.store.url();
    
    const method = props.isEditing ? 'patch' : 'post';
    
    router[method](url, submitData, {
        onSuccess: () => {
            emit('success');
        },
        onError: (validationErrors) => {
            Object.assign(errors, validationErrors);
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

// Cancel form
const cancel = () => {
    emit('cancel');
};
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
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Container Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="truck">Truck</Label>
                        <Input
                            id="truck"
                            v-model="form.truck"
                            type="text"
                            placeholder="TRK-001"
                            :class="{ 'border-red-500': errors.truck }"
                        />
                        <InputError v-if="errors.truck" :message="errors.truck" />
                    </div>
                    
                    <div class="space-y-2">
                        <Label for="container_number">Container Number</Label>
                        <Input
                            id="container_number"
                            v-model="form.container_number"
                            type="text"
                            placeholder="CONT1234567"
                            maxlength="11"
                            :class="{ 'border-red-500': errors.container_number }"
                        />
                        <InputError v-if="errors.container_number" :message="errors.container_number" />
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
                                min="1"
                                placeholder="150"
                                :class="{ 'border-red-500': errors.quantity_of_bags }"
                            />
                            <InputError v-if="errors.quantity_of_bags" :message="errors.quantity_of_bags" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="w_jute_bag">Jute Bag Weight (kg)</Label>
                            <Input
                                id="w_jute_bag"
                                v-model.number="form.w_jute_bag"
                                type="number"
                                step="0.01"
                                min="0.01"
                                max="99.99"
                                placeholder="1.50"
                                :class="{ 'border-red-500': errors.w_jute_bag }"
                            />
                            <InputError v-if="errors.w_jute_bag" :message="errors.w_jute_bag" />
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
                                :class="{ 'border-red-500': errors.w_total }"
                            />
                            <InputError v-if="errors.w_total" :message="errors.w_total" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="w_truck">Truck Weight (kg)</Label>
                            <Input
                                id="w_truck"
                                v-model.number="form.w_truck"
                                type="number"
                                min="1"
                                placeholder="10000"
                                :class="{ 'border-red-500': errors.w_truck }"
                            />
                            <InputError v-if="errors.w_truck" :message="errors.w_truck" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="w_container">Container Weight (kg)</Label>
                            <Input
                                id="w_container"
                                v-model.number="form.w_container"
                                type="number"
                                min="1"
                                placeholder="2500"
                                :class="{ 'border-red-500': errors.w_container }"
                            />
                            <InputError v-if="errors.w_container" :message="errors.w_container" />
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
                                :class="{ 'border-red-500': errors.w_dunnage_dribag }"
                            />
                            <InputError v-if="errors.w_dunnage_dribag" :message="errors.w_dunnage_dribag" />
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
                    <div v-if="calculationStatus.gross === 'pending' || calculationStatus.tare === 'pending' || calculationStatus.net === 'pending'" 
                         class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                        <p class="text-sm text-blue-700">
                            <Calculator class="h-4 w-4 inline mr-1" />
                            Please fill in all required fields to see calculated weights
                        </p>
                    </div>
                    
                    <!-- Validation Errors -->
                    <div v-if="!validationRules.isValid" class="p-3 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm font-medium text-red-700 mb-2">Calculation Issues:</p>
                        <ul class="text-sm text-red-600 space-y-1">
                            <li v-for="issue in validationRules.issues" :key="issue" class="flex items-start gap-1">
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
                        :class="{ 'border-red-500': errors.note }"
                    />
                    <InputError v-if="errors.note" :message="errors.note" />
                </div>
                
                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <Button type="button" variant="outline" @click="cancel">
                        <X class="h-4 w-4 mr-2" />
                        Cancel
                    </Button>
                    <Button 
                        type="submit" 
                        :disabled="processing || !validationRules.isValid"
                        class="min-w-[120px]"
                    >
                        <Save class="h-4 w-4 mr-2" />
                        {{ processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>