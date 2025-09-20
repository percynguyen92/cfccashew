<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Container, type CuttingTest } from '@/types';
import * as containerRoutes from '@/routes/containers';
import * as billRoutes from '@/routes/bills';
import * as cuttingTestRoutes from '@/routes/cutting-tests';
import { Head, router } from '@inertiajs/vue3';
import {
    Package,
    FileText,
    Truck,
    Scale,
    Droplets,
    Target,
    Plus,
    ArrowLeft,
    Edit
} from 'lucide-vue-next';

interface Props {
    container: Container;
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

// Format weight display
const formatWeight = (weight: number | null): string => {
    if (weight === null || weight === undefined) return '-';
    return weight.toLocaleString();
};

// Format decimal weight display
const formatDecimalWeight = (weight: number | null): string => {
    if (weight === null || weight === undefined) return '-';
    return weight.toFixed(2);
};

// Format moisture display
const formatMoisture = (moisture: number | null | undefined): string => {
    if (moisture === null || moisture === undefined) return '-';
    return `${moisture.toFixed(1)}%`;
};

// Format outurn display
const formatOuturn = (outurn: number | null | undefined): string => {
    if (outurn === null || outurn === undefined) return '-';
    return `${outurn.toFixed(2)} lbs/80kg`;
};

// Format defective ratio
const formatDefectiveRatio = (test: CuttingTest): string => {
    if (!test.w_defective_nut || !test.w_defective_kernel) return '-';
    const ratio = test.w_defective_nut / 2;
    return `${test.w_defective_nut}/${ratio.toFixed(1)}`;
};

// Get cutting test type label
const getTestTypeLabel = (type: number): string => {
    const labels = {
        1: 'Final Sample 1st Cut',
        2: 'Final Sample 2nd Cut',
        3: 'Final Sample 3rd Cut',
        4: 'Container Cut'
    };
    return labels[type as keyof typeof labels] || `Type ${type}`;
};

// Get cutting test type badge variant
const getTestTypeBadgeVariant = (type: number): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (type === 4) return 'default';
    return 'secondary';
};

// Navigate functions
const goBack = () => {
    router.visit(containerRoutes.index.url());
};

const editContainer = () => {
    router.visit(containerRoutes.edit.url(props.container.id));
};

const viewBill = () => {
    if (props.container.bill) {
        router.visit(billRoutes.show.url(props.container.bill.slug || props.container.bill.id));
    }
};

const addCuttingTest = () => {
    router.visit(cuttingTestRoutes.create.url({ query: { container_id: props.container.id, bill_id: props.container.bill_id } }));
};
</script>

<template>

    <Head :title="`Container ${container.container_number || `#${container.id}`}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" @click="goBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Containers
                    </Button>
                    <div class="flex items-center gap-2">
                        <Package class="h-6 w-6" />
                        <h1 class="text-2xl font-semibold">
                            Container {{ container.container_number || `#${container.id}` }}
                        </h1>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="editContainer">
                        <Edit class="h-4 w-4 mr-2" />
                        Edit Container
                    </Button>
                    <Button @click="addCuttingTest">
                        <Plus class="h-4 w-4 mr-2" />
                        Add Cutting Test
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Container Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Package class="h-5 w-5" />
                                Container Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Container Number</label>
                                    <p class="text-lg font-medium">{{ container.container_number || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Truck</label>
                                    <p class="text-lg font-medium flex items-center gap-2">
                                        <Truck class="h-4 w-4" />
                                        {{ container.truck || '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Quantity of Bags</label>
                                    <p class="text-lg font-medium">{{ container.quantity_of_bags || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Jute Bag Weight</label>
                                    <p class="text-lg font-medium">{{ formatDecimalWeight(container.w_jute_bag) }} kg
                                    </p>
                                </div>
                            </div>

                            <Separator />

                            <!-- Weight Information -->
                            <div>
                                <h4 class="font-medium mb-3 flex items-center gap-2">
                                    <Scale class="h-4 w-4" />
                                    Weight Information
                                </h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Total Weight</label>
                                        <p class="font-medium">{{ formatWeight(container.w_total) }} kg</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Truck Weight</label>
                                        <p class="font-medium">{{ formatWeight(container.w_truck) }} kg</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Container
                                            Weight</label>
                                        <p class="font-medium">{{ formatWeight(container.w_container) }} kg</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Gross Weight</label>
                                        <p class="font-medium">{{ formatWeight(container.w_gross) }} kg</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Dunnage/Dribag</label>
                                        <p class="font-medium">{{ formatWeight(container.w_dunnage_dribag) }} kg</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Tare Weight</label>
                                        <p class="font-medium">{{ formatDecimalWeight(container.w_tare) }} kg</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-sm font-medium text-muted-foreground">Net Weight</label>
                                        <p class="text-lg font-semibold text-green-600">{{
                                            formatDecimalWeight(container.w_net) }} kg</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="container.note">
                                <Separator />
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Notes</label>
                                    <p class="mt-1">{{ container.note }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Cutting Tests -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Target class="h-5 w-5" />
                                    Cutting Tests
                                </div>
                                <Button size="sm" @click="addCuttingTest">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Add Test
                                </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="container.cutting_tests && container.cutting_tests.length > 0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Type</TableHead>
                                            <TableHead>Moisture</TableHead>
                                            <TableHead>Sample Weight</TableHead>
                                            <TableHead>Nut Count</TableHead>
                                            <TableHead>Defective Ratio</TableHead>
                                            <TableHead>Outurn Rate</TableHead>
                                            <TableHead>Date</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="test in container.cutting_tests" :key="test.id">
                                            <TableCell>
                                                <Badge :variant="getTestTypeBadgeVariant(test.type)">
                                                    {{ getTestTypeLabel(test.type) }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <span :class="test.moisture && test.moisture > 11
                                                    ? 'text-red-600 font-medium'
                                                    : ''">
                                                    {{ formatMoisture(test.moisture) }}
                                                </span>
                                            </TableCell>
                                            <TableCell>{{ test.sample_weight }}g</TableCell>
                                            <TableCell>{{ test.nut_count || '-' }}</TableCell>
                                            <TableCell>{{ formatDefectiveRatio(test) }}</TableCell>
                                            <TableCell>{{ formatOuturn(test.outturn_rate) }}</TableCell>
                                            <TableCell>
                                                <div class="text-sm text-muted-foreground">
                                                    {{ new Date(test.created_at).toLocaleDateString() }}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Target class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p class="text-lg font-medium mb-2">No cutting tests yet</p>
                                <p class="mb-4">Add cutting tests to track quality metrics for this container.</p>
                                <Button @click="addCuttingTest">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Add First Test
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Bill Information -->
                    <Card v-if="container.bill">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Bill Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Bill Number</label>
                                <p class="text-lg font-medium">
                                    {{ container.bill.bill_number || `Bill #${container.bill.id}` }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Seller</label>
                                <p class="font-medium">{{ container.bill.seller || '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Buyer</label>
                                <p class="font-medium">{{ container.bill.buyer || '-' }}</p>
                            </div>
                            <Button variant="outline" class="w-full" @click="viewBill">
                                <FileText class="h-4 w-4 mr-2" />
                                View Bill Details
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Quality Summary -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Droplets class="h-5 w-5" />
                                Quality Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Average Moisture</label>
                                <p class="text-lg font-medium">
                                    <span :class="container.average_moisture && container.average_moisture > 11
                                        ? 'text-red-600'
                                        : 'text-green-600'">
                                        {{ formatMoisture(container.average_moisture) }}
                                    </span>
                                </p>
                                <p v-if="container.average_moisture && container.average_moisture > 11"
                                    class="text-sm text-red-600 mt-1">
                                    Warning: High moisture content
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Outurn Rate</label>
                                <p class="text-lg font-medium text-blue-600">
                                    {{ formatOuturn(container.outturn_rate) }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tests Count</label>
                                <p class="text-lg font-medium">
                                    {{ container.cutting_tests?.length || 0 }} tests
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Container Metadata -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Container Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Created</label>
                                <p class="text-sm">{{ new Date(container.created_at).toLocaleString() }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
                                <p class="text-sm">{{ new Date(container.updated_at).toLocaleString() }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>





