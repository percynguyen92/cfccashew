<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { ChevronDown, ChevronRight, Plus, Package, TestTube } from 'lucide-vue-next';
import { ref } from 'vue';
import type { Bill } from '@/types';

interface Props {
    bill: Bill;
}

defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

// Track expanded containers
const expandedContainers = ref<Set<number>>(new Set());

const toggleContainer = (containerId: number) => {
    if (expandedContainers.value.has(containerId)) {
        expandedContainers.value.delete(containerId);
    } else {
        expandedContainers.value.add(containerId);
    }
};

const formatWeight = (weight: number | null | undefined): string => {
    if (weight === null || weight === undefined) return '-';
    return weight.toLocaleString();
};

const formatMoisture = (moisture: number | null | undefined): string => {
    if (moisture === null || moisture === undefined) return '-';
    return `${moisture.toFixed(1)}%`;
};

const formatOutturn = (outturn: number | null | undefined): string => {
    if (outturn === null || outturn === undefined) return '-';
    return `${outturn.toFixed(2)} lbs/80kg`;
};

const getTestTypeColor = (type: number): string => {
    switch (type) {
        case 1: return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 2: return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 3: return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 4: return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};
</script>

<template>

    <Head :title="`Bill #${bill.bill_number || bill.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Bill #{{ bill.bill_number || bill.id }}
                    </h1>
                    <p class="text-muted-foreground">
                        Created {{ new Date(bill.created_at).toLocaleDateString() }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/bills/${bill.slug}/edit`">
                        Edit Bill
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Bill Information Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Bill Information</CardTitle>
                    <CardDescription>Basic details about this Bill of Lading</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Bill Number</label>
                            <p class="text-lg font-semibold">{{ bill.bill_number || '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Seller</label>
                            <p class="text-lg font-semibold">{{ bill.seller || '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Buyer</label>
                            <p class="text-lg font-semibold">{{ bill.buyer || '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Average Outturn</label>
                            <p class="text-lg font-semibold">{{ formatOutturn(bill.average_outurn) }}</p>
                        </div>
                    </div>
                    <div v-if="bill.note" class="mt-4">
                        <label class="text-sm font-medium text-muted-foreground">Note</label>
                        <p class="mt-1 text-sm">{{ bill.note }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Final Samples Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <TestTube class="h-5 w-5" />
                                Final Samples
                            </CardTitle>
                            <CardDescription>Final sample cutting tests for this bill</CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Button size="sm" variant="outline" as-child>
                                <Link :href="`/cutting-tests/create?bill_id=${bill.id}&type=1`">
                                <Plus class="h-4 w-4 mr-1" />
                                Add Type 1
                                </Link>
                            </Button>
                            <Button size="sm" variant="outline" as-child>
                                <Link :href="`/cutting-tests/create?bill_id=${bill.id}&type=2`">
                                <Plus class="h-4 w-4 mr-1" />
                                Add Type 2
                                </Link>
                            </Button>
                            <Button size="sm" variant="outline" as-child>
                                <Link :href="`/cutting-tests/create?bill_id=${bill.id}&type=3`">
                                <Plus class="h-4 w-4 mr-1" />
                                Add Type 3
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="bill.final_samples && bill.final_samples.length > 0" class="space-y-4">
                        <div v-for="sample in bill.final_samples" :key="sample.id"
                            class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex items-center gap-4">
                                <Badge :class="getTestTypeColor(sample.type)">
                                    {{ sample.type_label }}
                                </Badge>
                                <div class="grid grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="text-muted-foreground">Moisture:</span>
                                        <span class="ml-1 font-medium">{{ formatMoisture(sample.moisture) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Sample Weight:</span>
                                        <span class="ml-1 font-medium">{{ formatWeight(sample.sample_weight) }}g</span>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Outturn:</span>
                                        <span class="ml-1 font-medium">{{ formatOutturn(sample.outturn_rate) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Date:</span>
                                        <span class="ml-1 font-medium">{{ new
                                            Date(sample.created_at).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <TestTube class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p>No final samples recorded yet</p>
                        <p class="text-sm">Add final sample cutting tests to track quality metrics</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Containers Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <Package class="h-5 w-5" />
                                Containers
                            </CardTitle>
                            <CardDescription>Containers associated with this bill</CardDescription>
                        </div>
                        <Button as-child>
                            <Link :href="`/containers/create?bill_id=${bill.id}`">
                            <Plus class="h-4 w-4 mr-2" />
                            Add Container
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="bill.containers && bill.containers.length > 0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-12"></TableHead>
                                    <TableHead>Container #</TableHead>
                                    <TableHead>Truck</TableHead>
                                    <TableHead>Bags</TableHead>
                                    <TableHead>Net Weight</TableHead>
                                    <TableHead>Moisture</TableHead>
                                    <TableHead>Outturn</TableHead>
                                    <TableHead>Tests</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-for="container in bill.containers" :key="container.id">
                                    <TableRow class="cursor-pointer hover:bg-muted/50"
                                        @click="toggleContainer(container.id)">
                                        <TableCell>
                                            <ChevronRight v-if="!expandedContainers.has(container.id)"
                                                class="h-4 w-4 transition-transform" />
                                            <ChevronDown v-else class="h-4 w-4 transition-transform" />
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            {{ container.container_number || '-' }}
                                        </TableCell>
                                        <TableCell>{{ container.truck || '-' }}</TableCell>
                                        <TableCell>{{ container.quantity_of_bags || '-' }}</TableCell>
                                        <TableCell>{{ formatWeight(container.w_net) }}</TableCell>
                                        <TableCell>{{ formatMoisture(container.average_moisture) }}</TableCell>
                                        <TableCell>{{ formatOutturn(container.outturn_rate) }}</TableCell>
                                        <TableCell>
                                            <Badge variant="secondary">
                                                {{ container.cutting_tests?.length || 0 }} tests
                                            </Badge>
                                        </TableCell>
                                    </TableRow>

                                    <!-- Expandable Container Details -->
                                    <TableRow v-if="expandedContainers.has(container.id)">
                                        <TableCell colspan="8" class="p-0">
                                            <div class="bg-muted/30 p-4 space-y-4">
                                                <!-- Container Details -->
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-muted-foreground">Gross Weight:</span>
                                                        <span class="ml-1 font-medium">{{
                                                            formatWeight(container.w_gross) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-muted-foreground">Tare Weight:</span>
                                                        <span class="ml-1 font-medium">{{ formatWeight(container.w_tare)
                                                            }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-muted-foreground">Container Weight:</span>
                                                        <span class="ml-1 font-medium">{{
                                                            formatWeight(container.w_container) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-muted-foreground">Truck Weight:</span>
                                                        <span class="ml-1 font-medium">{{
                                                            formatWeight(container.w_truck) }}</span>
                                                    </div>
                                                </div>

                                                <!-- Cutting Tests -->
                                                <div>
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h4 class="font-medium">Cutting Tests</h4>
                                                        <Button size="sm" variant="outline" as-child>
                                                            <Link
                                                                :href="`/cutting-tests/create?bill_id=${bill.id}&container_id=${container.id}&type=4`">
                                                            <Plus class="h-4 w-4 mr-1" />
                                                            Add Test
                                                            </Link>
                                                        </Button>
                                                    </div>

                                                    <div v-if="container.cutting_tests && container.cutting_tests.length > 0"
                                                        class="space-y-2">
                                                        <div v-for="test in container.cutting_tests" :key="test.id"
                                                            class="flex items-center justify-between p-3 bg-background border rounded">
                                                            <div class="flex items-center gap-4">
                                                                <Badge :class="getTestTypeColor(test.type)">
                                                                    {{ test.type_label }}
                                                                </Badge>
                                                                <div class="grid grid-cols-4 gap-4 text-sm">
                                                                    <div>
                                                                        <span
                                                                            class="text-muted-foreground">Moisture:</span>
                                                                        <span class="ml-1 font-medium">{{
                                                                            formatMoisture(test.moisture) }}</span>
                                                                    </div>
                                                                    <div>
                                                                        <span
                                                                            class="text-muted-foreground">Sample:</span>
                                                                        <span class="ml-1 font-medium">{{
                                                                            formatWeight(test.sample_weight) }}g</span>
                                                                    </div>
                                                                    <div>
                                                                        <span
                                                                            class="text-muted-foreground">Outturn:</span>
                                                                        <span class="ml-1 font-medium">{{
                                                                            formatOutturn(test.outturn_rate) }}</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="text-muted-foreground">Date:</span>
                                                                        <span class="ml-1 font-medium">{{ new
                                                                            Date(test.created_at).toLocaleDateString()
                                                                            }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div v-else class="text-center py-4 text-muted-foreground text-sm">
                                                        No cutting tests recorded for this container
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <Package class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p>No containers added yet</p>
                        <p class="text-sm">Add containers to track weights and cutting tests</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>