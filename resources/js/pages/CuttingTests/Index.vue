<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pagination, PaginationList, PaginationListItem } from '@/components/ui/pagination';
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
import { usePagination } from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { debounce } from 'lodash-es';
import { computed, onBeforeUnmount, reactive } from 'vue';

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

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

interface Props {
    cutting_tests: CuttingTest[];
    pagination: PaginationData;
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
const cuttingTests = computed(() => props.cutting_tests);

const { breadcrumbs } = useBreadcrumbs();
const { goToPage } = usePagination();

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

const pagination = computed(() => props.pagination);

const totalTests = computed(() => pagination.value.total);
const currentFrom = computed(() => pagination.value.from ?? 0);
const currentTo = computed(() => pagination.value.to ?? 0);

const paginationLinks = computed(() => {
    const links = pagination.value.links ?? [];
    return links.slice(1, links.length - 1);
});

const previousLink = computed<PaginationLink>(() => {
    const links = pagination.value.links ?? [];
    return links[0] ?? { url: null, label: 'Previous', active: false };
});

const nextLink = computed<PaginationLink>(() => {
    const links = pagination.value.links ?? [];
    return links[links.length - 1] ?? { url: null, label: 'Next', active: false };
});

function cleanFilters(filterObj: typeof filters) {
    const cleaned: Record<string, string> = {};

    Object.entries(filterObj).forEach(([key, value]) => {
        if (!value) {
            return;
        }

        if (key === 'test_type' && value === 'all') {
            return;
        }

        cleaned[key] = value;
    });

    return cleaned;
}

const submitFilters = (params: Record<string, string>) => {
    router.get('/cutting-tests', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const handleSearch = () => {
    submitFilters(cleanFilters(filters));
};

const debouncedSearch = debounce(() => {
    handleSearch();
}, 400);

const handleFilterChange = () => {
    debouncedSearch();
};

const clearFilters = () => {
    debouncedSearch.cancel();
    filters.bill_number = '';
    filters.test_type = 'all';
    filters.container_id = '';
    filters.moisture_min = '';
    filters.moisture_max = '';
    filters.date_from = '';
    filters.date_to = '';
    submitFilters({});
};

onBeforeUnmount(() => {
    debouncedSearch.cancel();
});

function createCuttingTest() {
    router.visit('/cutting-tests/create');
}

function viewCuttingTest(id: number) {
    router.visit(`/cutting-tests/${id}`);
}

function editCuttingTest(id: number) {
    router.visit(`/cutting-tests/${id}/edit`);
}

function deleteCuttingTest(id: number) {
    if (confirm('Are you sure you want to delete this cutting test?')) {
        router.delete(`/cutting-tests/${id}`);
    }
}

function getBillDisplay(test: CuttingTest) {
    if (test.bill?.bill_number) {
        return test.bill.bill_number;
    }
    return `Bill #${test.bill_id}`;
}

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
</script>

<template>
    <Head title="Cutting Tests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Cutting Tests</h1>
                    <p class="text-sm text-muted-foreground">
                        Monitor quality metrics across containers and samples.
                    </p>
                </div>
                <Button @click="createCuttingTest">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Cutting Test
                </Button>
            </div>

            <Card class="space-y-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="space-y-2">
                        <Label for="bill_number">Bill Number</Label>
                        <Input
                            id="bill_number"
                            v-model="filters.bill_number"
                            placeholder="Search bill number..."
                            @input="handleFilterChange"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="test_type">Test Type</Label>
                        <Select
                            v-model="filters.test_type"
                            @update:model-value="handleFilterChange"
                        >
                            <SelectTrigger id="test_type">
                                <SelectValue placeholder="All tests" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Tests</SelectItem>
                                <SelectItem value="final">Final Tests</SelectItem>
                                <SelectItem value="container">Container Tests</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="container_id">Container</Label>
                        <Input
                            id="container_id"
                            v-model="filters.container_id"
                            placeholder="Container number..."
                            @input="handleFilterChange"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label>Moisture Range (%)</Label>
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
                    <div class="space-y-2">
                        <Label for="date_from">Date From</Label>
                        <Input
                            id="date_from"
                            v-model="filters.date_from"
                            type="date"
                            @change="handleSearch"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_to">Date To</Label>
                        <Input
                            id="date_to"
                            v-model="filters.date_to"
                            type="date"
                            @change="handleSearch"
                        />
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button size="sm" @click="handleSearch">
                        <Search class="mr-2 h-4 w-4" />
                        Search
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters">
                        Clear Filters
                    </Button>
                    <span class="ml-auto text-sm text-muted-foreground">
                        {{ totalTests }} cutting tests total
                    </span>
                </div>
            </Card>

            <Card class="flex-1">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Bill</TableHead>
                                <TableHead>Sample / Container</TableHead>
                                <TableHead class="text-right">Moisture (%)</TableHead>
                                <TableHead class="text-right">Defective Nut (g)</TableHead>
                                <TableHead class="text-right">Good Kernel (g)</TableHead>
                                <TableHead class="text-right">Outturn (lbs/80kg)</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="w-[140px] text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="cuttingTests.length === 0">
                                <TableCell colspan="8" class="py-8 text-center text-muted-foreground">
                                    No cutting tests found. Try adjusting your search filters.
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-for="test in cuttingTests"
                                :key="test.id"
                                class="cursor-pointer hover:bg-muted/50"
                                @click="viewCuttingTest(test.id)"
                            >
                                <TableCell class="font-medium">
                                    <button
                                        type="button"
                                        class="text-primary underline-offset-4 hover:underline"
                                        @click.stop="router.visit(`/bills/${test.bill_id}`)"
                                    >
                                        {{ getBillDisplay(test) }}
                                    </button>
                                    <div
                                        v-if="test.bill?.seller || test.bill?.buyer"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ test.bill?.seller || '—' }} /
                                        {{ test.bill?.buyer || '—' }}
                                    </div>
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
                                                type="button"
                                                class="text-primary underline-offset-4 hover:underline"
                                                @click.stop="
                                                    router.visit(
                                                        `/containers/${
                                                            test.container?.container_number ||
                                                            test.container_id
                                                        }`,
                                                    )
                                                "
                                            >
                                                {{ getContainerDisplay(test) }}
                                            </button>
                                            <span v-else class="text-muted-foreground">
                                                {{ getContainerDisplay(test) }}
                                            </span>
                                        </template>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right font-mono">
                                    <span v-if="test.moisture_formatted">
                                        {{ test.moisture_formatted }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell class="text-right font-mono">
                                    <span v-if="test.defective_nut_formatted">
                                        {{ test.defective_nut_formatted }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell class="text-right font-mono">
                                    <span v-if="test.w_good_kernel !== null && test.w_good_kernel !== undefined">
                                        {{ test.w_good_kernel }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell class="text-right font-mono">
                                    <span v-if="test.outturn_rate_formatted">
                                        {{ test.outturn_rate_formatted }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm text-muted-foreground">
                                        {{ new Date(test.created_at).toLocaleDateString() }}
                                    </div>
                                </TableCell>
                                <TableCell class="flex items-center justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label="View cutting test"
                                        @click.stop="viewCuttingTest(test.id)"
                                    >
                                        <Eye class="h-4 w-4" />
                                        <span class="sr-only">View cutting test</span>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label="Edit cutting test"
                                        @click.stop="editCuttingTest(test.id)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        <span class="sr-only">Edit cutting test</span>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        aria-label="Delete cutting test"
                                        @click.stop="deleteCuttingTest(test.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span class="sr-only">Delete cutting test</span>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </Card>

            <div
                v-if="cuttingTests.length > 0 && pagination.last_page > 1"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Showing {{ currentFrom }} to {{ currentTo }} of {{ totalTests }} results
                </div>
                <Pagination>
                    <PaginationList>
                        <PaginationListItem>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!previousLink.url"
                                @click="goToPage(previousLink.url)"
                            >
                                Previous
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem
                            v-for="link in paginationLinks"
                            :key="link.label"
                        >
                            <Button
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                :disabled="!link.url"
                                @click="goToPage(link.url)"
                            >
                                <span v-html="link.label" />
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!nextLink.url"
                                @click="goToPage(nextLink.url)"
                            >
                                Next
                            </Button>
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>

