<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Container } from '@/types';
import * as containerRoutes from '@/routes/containers';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Package, ArrowLeft, Save, Calculator } from 'lucide-vue-next';
import { computed, watch } from 'vue';

interface Props {
    container: Container;
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

// Form setup with existing container data
const form = useForm({
    bill_id: props.container.bill_id,
    truck: props.container.truck || '',
    container_number: props.container.container_number || '',
    quantity_of_bags: props.container.quantity_of_bags,
    w_jute_bag: props.container.w_jute_bag || 1.00,
    w_total: props.container.w_total,
    w_truck: props.container.w_truck,
    w_container: props.container.w_container,
    w_gross: props.container.w_gross,
    w_dunnage_dribag: props.container.w_dunnage_dribag,
    w_tare: props.container.w_tare,
    w_net: props.container.w_net,
    note: props.container.note || '',
});

// Auto-calculate weights
const calculatedTare = computed(() => {
    const truck = form.w_truck || 0;
    const container = form.w_container || 0;
    const dunnage = form.w_dunnage_dribag || 0;
    return truck + container + dunnage;
});

const calculatedNet = computed(() => {
    const gross = form.w_gross || 0;
    const tare = form.w_tare || calculatedTare.value;
    return gross - tare;
});

// Watch for changes and auto-calculate
watch([() => form.w_truck, () => form.w_container, () => form.w_dunnage_dribag], () => {
    form.w_tare = calculatedTare.value;
});

watch([() => form.w_gross, () => form.w_tare], () => {
    if (form.w_gross && form.w_tare) {
        form.w_net = calculatedNet.value;
    }
});

// Form submission
const submit = () => {
    form.put(containerRoutes.update.url(props.container.id), {
        onSuccess: () => {
            // Will redirect to bill detail page as per controller
        },
    });
};

// Navigation
const goBack = () => {
    router.visit(containerRoutes.show.url(props.container.id));
};

// Auto-calculate helper
const autoCalculate = () => {
    if (form.w_truck && form.w_container && form.w_dunnage_dribag) {
        form.w_tare = calculatedTare.value;
    }
    if (form.w_gross && form.w_tare) {
        form.w_net = calculatedNet.value;
    }
};
</script>

<template>

    <Head :title="`Edit Container ${container.container_number || `#${container.id}`}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" @click="goBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Container
                    </Button>
                    <div class="flex items-center gap-2">
                        <Package class="h-6 w-6" />
                        <h1 class="text-2xl font-semibold">
                            Edit Container {{ container.container_number || `#${container.id}` }}
                        </h1>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Container Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="container_number">Container Number</Label>
                                        <Input id="container_number" v-model="form.container_number"
                                            placeholder="e.g., ABCD1234567" maxlength="11"
                                            :class="{ 'border-red-500': form.errors.container_number }" />
                                        <p v-if="form.errors.container_number" class="text-sm text-red-600">
                                            {{ form.errors.container_number }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            ISO format: 4 letters + 7 digits
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="truck">Truck</Label>
                                        <Input id="truck" v-model="form.truck" placeholder="Truck identifier"
                                            maxlength="20" :class="{ 'border-red-500': form.errors.truck }" />
                                        <p v-if="form.errors.truck" class="text-sm text-red-600">
                                            {{ form.errors.truck }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="quantity_of_bags">Quantity of Bags</Label>
                                        <Input id="quantity_of_bags" v-model.number="form.quantity_of_bags"
                                            type="number" min="0" placeholder="Number of bags"
                                            :class="{ 'border-red-500': form.errors.quantity_of_bags }" />
                                        <p v-if="form.errors.quantity_of_bags" class="text-sm text-red-600">
                                            {{ form.errors.quantity_of_bags }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="w_jute_bag">Jute Bag Weight (kg)</Label>
                                        <Input id="w_jute_bag" v-model.number="form.w_jute_bag" type="number"
                                            step="0.01" min="0" max="99.99"
                                            :class="{ 'border-red-500': form.errors.w_jute_bag }" />
                                        <p v-if="form.errors.w_jute_bag" class="text-sm text-red-600">
                                            {{ form.errors.w_jute_bag }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Weight Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center justify-between">
                                    Weight Information
                                    <Button type="button" variant="outline" size="sm" @click="autoCalculate">
                                        <Calculator class="h-4 w-4 mr-2" />
                                        Auto Calculate
                                    </Button>
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Input Weights -->
                                <div>
                                    <h4 class="font-medium mb-3">Input Weights</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="w_total">Total Weight (kg)</Label>
                                            <Input id="w_total" v-model.number="form.w_total" type="number" min="0"
                                                placeholder="Total weight"
                                                :class="{ 'border-red-500': form.errors.w_total }" />
                                            <p v-if="form.errors.w_total" class="text-sm text-red-600">
                                                {{ form.errors.w_total }}
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="w_gross">Gross Weight (kg)</Label>
                                            <Input id="w_gross" v-model.number="form.w_gross" type="number" min="0"
                                                placeholder="Gross weight"
                                                :class="{ 'border-red-500': form.errors.w_gross }" />
                                            <p v-if="form.errors.w_gross" class="text-sm text-red-600">
                                                {{ form.errors.w_gross }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <!-- Tare Components -->
                                <div>
                                    <h4 class="font-medium mb-3">Tare Components</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-2">
                                            <Label for="w_truck">Truck Weight (kg)</Label>
                                            <Input id="w_truck" v-model.number="form.w_truck" type="number" min="0"
                                                placeholder="Truck weight"
                                                :class="{ 'border-red-500': form.errors.w_truck }" />
                                            <p v-if="form.errors.w_truck" class="text-sm text-red-600">
                                                {{ form.errors.w_truck }}
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="w_container">Container Weight (kg)</Label>
                                            <Input id="w_container" v-model.number="form.w_container" type="number"
                                                min="0" placeholder="Container weight"
                                                :class="{ 'border-red-500': form.errors.w_container }" />
                                            <p v-if="form.errors.w_container" class="text-sm text-red-600">
                                                {{ form.errors.w_container }}
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="w_dunnage_dribag">Dunnage/Dribag (kg)</Label>
                                            <Input id="w_dunnage_dribag" v-model.number="form.w_dunnage_dribag"
                                                type="number" min="0" placeholder="Dunnage weight"
                                                :class="{ 'border-red-500': form.errors.w_dunnage_dribag }" />
                                            <p v-if="form.errors.w_dunnage_dribag" class="text-sm text-red-600">
                                                {{ form.errors.w_dunnage_dribag }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <!-- Calculated Weights -->
                                <div>
                                    <h4 class="font-medium mb-3">Calculated Weights</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="w_tare">Tare Weight (kg)</Label>
                                            <Input id="w_tare" v-model.number="form.w_tare" type="number" step="0.01"
                                                min="0" placeholder="Auto-calculated"
                                                :class="{ 'border-red-500': form.errors.w_tare }" />
                                            <p v-if="form.errors.w_tare" class="text-sm text-red-600">
                                                {{ form.errors.w_tare }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                Auto: {{ calculatedTare.toFixed(2) }} kg
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="w_net">Net Weight (kg)</Label>
                                            <Input id="w_net" v-model.number="form.w_net" type="number" step="0.01"
                                                min="0" placeholder="Auto-calculated"
                                                class="font-semibold text-green-600"
                                                :class="{ 'border-red-500': form.errors.w_net }" />
                                            <p v-if="form.errors.w_net" class="text-sm text-red-600">
                                                {{ form.errors.w_net }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                Auto: {{ calculatedNet.toFixed(2) }} kg
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Notes -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Additional Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-2">
                                    <Label for="note">Notes</Label>
                                    <Textarea id="note" v-model="form.note"
                                        placeholder="Any additional notes about this container..." rows="3"
                                        :class="{ 'border-red-500': form.errors.note }" />
                                    <p v-if="form.errors.note" class="text-sm text-red-600">
                                        {{ form.errors.note }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Bill Information -->
                        <Card v-if="container.bill">
                            <CardHeader>
                                <CardTitle>Bill Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Bill Number</Label>
                                    <p class="font-medium">{{ container.bill.bill_number || `Bill #${container.bill.id}`
                                    }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Seller</Label>
                                    <p>{{ container.bill.seller || '-' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Buyer</Label>
                                    <p>{{ container.bill.buyer || '-' }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Form Actions -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Actions</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <Button type="submit" class="w-full" :disabled="form.processing">
                                    <Save class="h-4 w-4 mr-2" />
                                    {{ form.processing ? 'Updating...' : 'Update Container' }}
                                </Button>
                                <Button type="button" variant="outline" class="w-full" @click="goBack">
                                    Cancel
                                </Button>
                            </CardContent>
                        </Card>

                        <!-- Weight Calculation Help -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Weight Calculation</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-2 text-sm">
                                <p><strong>Tare Weight =</strong> Truck + Container + Dunnage</p>
                                <p><strong>Net Weight =</strong> Gross - Tare</p>
                                <p class="text-muted-foreground">
                                    Weights are automatically calculated when you enter the component values.
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Container Metadata -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Container Details</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                                    <p class="text-sm">{{ new Date(container.created_at).toLocaleString() }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Last Updated</Label>
                                    <p class="text-sm">{{ new Date(container.updated_at).toLocaleString() }}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>




