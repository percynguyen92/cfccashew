<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ChevronDown, Plus, Search, Filter } from 'lucide-vue-next';

interface CuttingTest {
    id: number;
    bill_id: number;
    container_id: number | null;
    type: 1 | 2 | 3 | 4;
    type_label: string;
    moisture: number | null;
    moisture_formatted: string | null;
    sample_weight: number;
    nut_count: number | null;
    w_reject_nut: number | null;
    w_defective_nut: number | null;
    w_defective_kernel: number | null;
    w_good_kernel: number | null;
    w_sample_after_cut: number | null;
    outturn_rate: number | null;
    outturn_rate_formatted: string | null;
    note: string | null;
    defective_ratio?: {
        defective_nut: number;
        defective_kernel: number;
        ratio: number;
        formatted: string;
    };
    is_final_sample: boolean;
    is_container_test: boolean;
    bill?: {
        id: number;
        bill_number: string | null;
        seller: string | null;
        buyer: string | null;
    };
    container?: {
        id: number;
        container_number: string | null;
        truck: string | null;
    };
    created_at: string;
    updated_at: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: any[];
}

interface Props {
    cutting_tests: CuttingTest[];
    pagination: Pagination;
    filters: {
        bill_number?: string;
        test_type?: string;
        container_id?: string;
        moisture_min?: string;
        moisture_max?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();
const { breadcrumbs } = useBreadcrumbs();

// Filter state
const filters = ref({
    bill_number: props.filters.bill_number || '',
    test_type: props.filters.test_type || '',
    container_id: props.filters.container_id || '',
    moisture_min: props.filters.moisture_min || '',
    moisture_max: props.filters.moisture_max || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

// Debounced search function
const debouncedSearch = debounce(() => {
    router.get('/cutting-tests', cleanFilters(filters.value), {
        preserveState: true,
        preserveScroll: true,
    });
}, 400);

// Clean empty filters
function cleanFilters(filterObj: any) {
    const cleaned: any = {};
    Object.keys(filterObj).forEach(key => {
        if (filterObj[key] && filterObj[key] !== '') {
            cleaned[key] = filterObj[key];
        }
    });
    return cleaned;
}

// Handle filter changes
function handleFilterChange() {
    debouncedSearch();
}

// Clear all filters
function clearFilters() {
    filters.value = {
        bill_number: '',
        test_type: '',
        container_id: '',
        moisture_min: '',
        moisture_max: '',
        date_from: '',
        date_to: '',
    };
    router.get('/cutting-tests');
}

// Get badge variant for test type
function getTestTypeBadgeVariant(type: number) {
    switch (type) {
        case 1:
        case 2:
        case 3:
            return 'default'; // Final samples - blue
        case 4:
            return 'secondary'; // Container cut - gray
        default:
            return 'outline';
    }
}

// Navigate to create page
function createCuttingTest() {
    router.visit('/cutting-tests/create');
}

// Navigate to detail page
function viewCuttingTest(id: number) {
    router.visit(`/cutting-tests/${id}`);
}

// Navigate to edit page
function editCuttingTest(id: number) {
    router.visit(`/cutting-tests/${id}/edit`);
}

// Delete cutting test
function deleteCuttingTest(id: number) {
    if (confirm('Are you sure you want to delete this cutting test?')) {
        router.delete(`/cutting-tests/${id}`);
    }
}

// Get bill display text
function getBillDisplay(test: CuttingTest) {
    if (test.bill?.bill_number) {
        return test.bill.bill_number;
    }
    return `Bill #${test.bill_id}`;
}

// Get container display text
function getContainerDisplay(test: CuttingTest) {
    if (test.is_final_sample) {
        return 'Final Sample';
    }
    if (test.container?.container_number) {
        return test.container.container_number;
    }
    return `Container #${test.container_id}`;
}

const totalTests = computed(() => props.pagination.total);
const currentFrom = computed(() => props.pagination.from || 0);
const currentTo = computed(() => props.pagination.to || 0);
</script>

<template>
    <Head title="Cutting Tests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Cutting Tests</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Showing {{ currentFrom }} to {{ currentTo }} of {{ totalTests }} tests
                    </p>
                </div>
                <Button @click="createCuttingTest" class="flex items-center gap-2">
                    <Plus class="h-4 w-4" />
                    Add Cutting Test
                </Button>
            </div>

            <!-- Filters Section -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Filter class="h-4 w-4" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Bill Number Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Bill Number</label>
                            <Input
                                v-model="filters.bill_number"
                                placeholder="Search bill number..."
                                @input="handleFilterChange"
                            />
                        </div>

                        <!-- Test Type Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Test Type</label>
                            <Select v-model="filters.test_type" @update:model-value="handleFilterChange">
                                <SelectTrigger>
                                    <SelectValue placeholder="All tests" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All Tests</SelectItem>
                                    <SelectItem value="1">Final Sample 1st</SelectItem>
                                    <SelectItem value="2">Final Sample 2nd</SelectItem>
                                    <SelectItem value="3">Final Sample 3rd</SelectItem>
                                    <SelectItem value="4">Container Cut</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Container Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Container</label>
                            <Input
                                v-model="filters.container_id"
                                placeholder="Container number..."
                                @input="handleFilterChange"
                            />
                        </div>

                        <!-- Moisture Range -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Moisture Range (%)</label>
                            <div class="flex gap-2">
                                <Input
                                    v-model="filters.moisture_min"
                                    placeholder="Min"
                                    type="number"
                                    step="0.1"
                                    @input="handleFilterChange"
                                />
                                <Input
                                    v-model="filters.moisture_max"
                                    placeholder="Max"
                                    type="number"
                                    step="0.1"
                                    @input="handleFilterChange"
                                />
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Date From</label>
                            <Input
                                v-model="filters.date_from"
                                type="date"
                                @change="handleFilterChange"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Date To</label>
                            <Input
                                v-model="filters.date_to"
                                type="date"
                                @change="handleFilterChange"
                            />
                        </div>

                        <!-- Clear Filters -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium invisible">Clear</label>
                            <Button variant="outline" @click="clearFilters" class="w-full">
                                Clear Filters
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Data Table -->
            <Card class="flex-1">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Bill</TableHead>
                                    <TableHead>Container</TableHead>
                                    <TableHead>Moisture</TableHead>
                                    <TableHead>Defective Nut</TableHead>
                                    <TableHead>Good Kernel</TableHead>
                                    <TableHead>Outturn Rate</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead class="w-[100px]">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="cutting_tests.length === 0">
                                    <TableCell colspan="9" class="text-center py-8 text-muted-foreground">
                                        No cutting tests found.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="test in cutting_tests" :key="test.id" class="cursor-pointer hover:bg-muted/50">
                                    <TableCell>
                                        <Badge :variant="getTestTypeBadgeVariant(test.type)">
                                            {{ test.type_label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <button
                                            @click="router.visit(`/bills/${test.bill_id}`)"
                                            class="text-blue-600 hover:text-blue-800 hover:underline"
                                        >
                                            {{ getBillDisplay(test) }}
                                        </button>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="test.is_final_sample" class="text-muted-foreground italic">
                                            Final Sample
                                        </span>
                                        <button
                                            v-else-if="test.container"
                                            @click="router.visit(`/containers/${test.container.container_number || test.container_id}`)"
                                            class="text-blue-600 hover:text-blue-800 hover:underline"
                                        >
                                            {{ getContainerDisplay(test) }}
                                        </button>
                                        <span v-else class="text-muted-foreground">
                                            {{ getContainerDisplay(test) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="test.moisture_formatted" class="font-mono">
                                            {{ test.moisture_formatted }}
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="test.defective_ratio" class="font-mono">
                                            {{ test.defective_ratio.formatted }}
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="test.w_good_kernel" class="font-mono">
                                            {{ test.w_good_kernel }}g
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="test.outturn_rate_formatted" class="font-mono">
                                            {{ test.outturn_rate_formatted }}
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground">
                                        {{ new Date(test.created_at).toLocaleDateString() }}
                                    </TableCell>
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm">
                                                    <ChevronDown class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="viewCuttingTest(test.id)">
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="editCuttingTest(test.id)">
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="deleteCuttingTest(test.id)" class="text-destructive">
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing {{ currentFrom }} to {{ currentTo }} of {{ totalTests }} results
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="pagination.current_page === 1"
                        @click="router.get('/cutting-tests', { ...cleanFilters(filters), page: pagination.current_page - 1 })"
                    >
                        Previous
                    </Button>
                    <div class="text-sm">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="pagination.current_page === pagination.last_page"
                        @click="router.get('/cutting-tests', { ...cleanFilters(filters), page: pagination.current_page + 1 })"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
