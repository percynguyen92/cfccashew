<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import { ChevronDown, Filter, Plus } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

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
    defective_nut_formatted: string | null;
    defective_kernel_formatted: string | null;
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
const filters = reactive({
    bill_number: props.filters.bill_number || '',
    test_type:
        props.filters.test_type && props.filters.test_type !== ''
            ? props.filters.test_type
            : 'all',
    container_id: props.filters.container_id || '',
    moisture_min: props.filters.moisture_min || '',
    moisture_max: props.filters.moisture_max || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const hasActiveFilters = computed(
    () => Object.keys(cleanFilters(filters)).length > 0,
);
const showFilters = ref(hasActiveFilters.value);
const filterButtonLabel = computed(() => (showFilters.value ? 'Hide Filters' : 'Filters'));
const filterButtonVariant = computed(() =>
    showFilters.value || hasActiveFilters.value ? 'secondary' : 'outline',
);

// Debounced search function
const debouncedSearch = debounce(() => {
    router.get('/cutting-tests', cleanFilters(filters), {
        preserveState: true,
        preserveScroll: true,
    });
}, 400);

// Clean empty filters
function cleanFilters(filterObj: any) {
    const cleaned: any = {};
    Object.keys(filterObj).forEach((key) => {
        const value = filterObj[key];
        if (!value || value === '' || value === 'all') {
            return;
        }
        cleaned[key] = value;
    });
    return cleaned;
}

// Handle filter changes
function handleFilterChange() {
    debouncedSearch();
}

function toggleFilters() {
    showFilters.value = !showFilters.value;
}

// Clear all filters
function clearFilters() {
    Object.assign(filters, {
        bill_number: '',
        test_type: 'all',
        container_id: '',
        moisture_min: '',
        moisture_max: '',
        date_from: '',
        date_to: '',
    });
    router.get('/cutting-tests');
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
function getFinalSampleLabel(type: number) {
    switch (type) {
        case 1:
            return '#1';
        case 2:
            return '#2';
        case 3:
            return '#3';
        default:
            return '#?';
    }
}

function getContainerDisplay(test: CuttingTest) {
    if (test.container?.container_number) {
        return test.container.container_number;
    }
    if (test.container_id) {
        return `Container #${test.container_id}`;
    }
    return 'Container';
}

const totalTests = computed(() => props.pagination.total);
const currentFrom = computed(() => props.pagination.from || 0);
const currentTo = computed(() => props.pagination.to || 0);
</script>

<template>
    <Head title="Cutting Tests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Cutting Tests</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Showing {{ currentFrom }} to {{ currentTo }} of
                        {{ totalTests }} tests
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        :variant="filterButtonVariant"
                        class="flex items-center gap-2"
                        @click="toggleFilters"
                    >
                        <Filter class="h-4 w-4" />
                        {{ filterButtonLabel }}
                    </Button>
                    <Button
                        @click="createCuttingTest"
                        class="flex items-center gap-2"
                    >
                        <Plus class="h-4 w-4" />
                        Add Cutting Test
                    </Button>
                </div>
            </div>

            <!-- Filters Section -->
            <Card v-if="showFilters">
                <CardHeader>
                    <CardTitle class="text-base">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Bill Number Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium"
                                >Bill Number</label
                            >
                            <Input
                                v-model="filters.bill_number"
                                placeholder="Search bill number..."
                                @input="handleFilterChange"
                            />
                        </div>

                        <!-- Test Type Filter -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Test Type</label>
                            <Select
                                v-model="filters.test_type"
                                @update:model-value="handleFilterChange"
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="All tests" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Tests</SelectItem>
                                    <SelectItem value="final">Final Tests</SelectItem>
                                    <SelectItem value="container">Container Tests</SelectItem>
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
                            <label class="text-sm font-medium"
                                >Moisture Range (%)</label
                            >
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
                            <label class="invisible text-sm font-medium"
                                >Clear</label
                            >
                            <Button
                                variant="outline"
                                @click="clearFilters"
                                class="w-full"
                            >
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
                                    <TableHead>Bill</TableHead>
                                    <TableHead>Sample / Container</TableHead>
                                    <TableHead>Moisture (%)</TableHead>
                                    <TableHead>Defective Nut (g)</TableHead>
                                    <TableHead>Good Kernel (g)</TableHead>
                                    <TableHead>Outturn (lbs/80kgs)</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead class="w-[100px]"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="cutting_tests.length === 0">
                                    <TableCell
                                        colspan="8"
                                        class="py-8 text-center text-muted-foreground"
                                    >
                                        No cutting tests found.
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="test in cutting_tests"
                                    :key="test.id"
                                    class="cursor-pointer hover:bg-muted/50"
                                >
                                    <TableCell>
                                        <button
                                            @click="
                                                router.visit(
                                                    `/bills/${test.bill_id}`,
                                                )
                                            "
                                            class="text-blue-600 hover:text-blue-800 hover:underline"
                                        >
                                            {{ getBillDisplay(test) }}
                                        </button>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2 text-sm">
                                            <template v-if="test.is_final_sample">
                                                <span class="rounded-md bg-muted px-2 py-0.5 font-semibold text-muted-foreground">
                                                    {{ getFinalSampleLabel(test.type) }}
                                                </span>
                                            </template>
                                            <template v-else>
                                                <button
                                                    v-if="test.container"
                                                    @click="
                                                        router.visit(
                                                            `/containers/${
                                                                test.container
                                                                    ?.container_number ||
                                                                test.container_id
                                                            }`,
                                                        )
                                                    "
                                                    class="text-blue-600 hover:text-blue-800 hover:underline"
                                                >
                                                    {{ getContainerDisplay(test) }}
                                                </button>
                                                <span
                                                    v-else
                                                    class="text-muted-foreground"
                                                >
                                                    {{ getContainerDisplay(test) }}
                                                </span>
                                            </template>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            v-if="test.moisture_formatted"
                                            class="font-mono"
                                        >
                                            {{ test.moisture_formatted }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                            >-</span
                                        >
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            v-if="
                                                test.defective_kernel_formatted
                                            "
                                            class="font-mono"
                                        >
                                            {{
                                                test.defective_kernel_formatted
                                            }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                            >-</span
                                        >
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            v-if="test.w_good_kernel"
                                            class="font-mono"
                                        >
                                            {{ test.w_good_kernel }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                            >-</span
                                        >
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            v-if="test.outturn_rate_formatted"
                                            class="font-mono"
                                        >
                                            {{ test.outturn_rate_formatted }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                            >-</span
                                        >
                                    </TableCell>
                                    <TableCell
                                        class="text-sm text-muted-foreground"
                                    >
                                        {{
                                            new Date(
                                                test.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    <ChevronDown
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem
                                                    @click="
                                                        viewCuttingTest(test.id)
                                                    "
                                                >
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    @click="
                                                        editCuttingTest(test.id)
                                                    "
                                                >
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    @click="
                                                        deleteCuttingTest(
                                                            test.id,
                                                        )
                                                    "
                                                    class="text-destructive"
                                                >
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
            <div
                v-if="pagination.last_page > 1"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Showing {{ currentFrom }} to {{ currentTo }} of
                    {{ totalTests }} results
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="pagination.current_page === 1"
                        @click="
                            router.get('/cutting-tests', {
                                ...cleanFilters(filters),
                                page: pagination.current_page - 1,
                            })
                        "
                    >
                        Previous
                    </Button>
                    <div class="text-sm">
                        Page {{ pagination.current_page }} of
                        {{ pagination.last_page }}
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="
                            pagination.current_page === pagination.last_page
                        "
                        @click="
                            router.get('/cutting-tests', {
                                ...cleanFilters(filters),
                                page: pagination.current_page + 1,
                            })
                        "
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
