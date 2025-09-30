<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Pagination,
    PaginationList,
    PaginationListItem,
} from '@/components/ui/pagination';
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
import { debounce } from 'lodash-es';
import { Eye, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, reactive } from 'vue';
import { useI18n } from 'vue-i18n';

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

const { t } = useI18n();
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
const placeholder = computed(() => t('common.placeholders.notAvailable'));

const paginationLinks = computed(() => {
    const links = pagination.value.links ?? [];
    return links.slice(1, links.length - 1);
});

const previousLink = computed<PaginationLink>(() => {
    const links = pagination.value.links ?? [];
    return (
        links[0] ?? {
            url: null,
            label: t('cuttingTests.index.pagination.previous'),
            active: false,
        }
    );
});

const nextLink = computed<PaginationLink>(() => {
    const links = pagination.value.links ?? [];
    return (
        links[links.length - 1] ?? {
            url: null,
            label: t('cuttingTests.index.pagination.next'),
            active: false,
        }
    );
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
    if (confirm(t('cuttingTests.index.dialog.delete.confirm'))) {
        router.delete(`/cutting-tests/${id}`);
    }
}

function getBillDisplay(test: CuttingTest) {
    if (test.bill?.bill_number) {
        return test.bill.bill_number;
    }
    return t('cuttingTests.index.table.billFallback', { id: test.bill_id });
}

function openBill(test: CuttingTest) {
    router.visit(`/bills/${test.bill_id}`);
}

function openContainer(test: CuttingTest) {
    const identifier = test.container?.container_number || test.container_id;

    if (!identifier) {
        return;
    }

    router.visit(`/containers/${identifier}`);
}

function getFinalSampleLabel(type: number) {
    switch (type) {
        case 1:
            return t('cuttingTests.index.table.finalSampleLabel', { order: 1 });
        case 2:
            return t('cuttingTests.index.table.finalSampleLabel', { order: 2 });
        case 3:
            return t('cuttingTests.index.table.finalSampleLabel', { order: 3 });
        default:
            return t('cuttingTests.index.table.finalSampleLabel', {
                order: '?',
            });
    }
}

function getContainerDisplay(test: CuttingTest) {
    if (test.container?.container_number) {
        return test.container.container_number;
    }
    if (test.container_id) {
        return t('cuttingTests.index.table.containerFallback', {
            id: test.container_id,
        });
    }
    return t('cuttingTests.index.table.containerPlaceholder');
}
</script>

<template>
    <Head :title="t('cuttingTests.index.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">
                        {{ t('cuttingTests.index.title') }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ t('cuttingTests.index.description') }}
                    </p>
                </div>
                <Button @click="createCuttingTest">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('cuttingTests.index.actions.create') }}
                </Button>
            </div>

            <Card class="gap-1 space-y-4 p-4">
                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="space-y-2">
                        <Label for="bill_number">
                            {{
                                t('cuttingTests.index.filters.billNumber.label')
                            }}
                        </Label>
                        <Input
                            id="bill_number"
                            v-model="filters.bill_number"
                            :placeholder="
                                t(
                                    'cuttingTests.index.filters.billNumber.placeholder',
                                )
                            "
                            @input="handleFilterChange"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="test_type">
                            {{ t('cuttingTests.index.filters.testType.label') }}
                        </Label>
                        <Select
                            v-model="filters.test_type"
                            @update:model-value="handleFilterChange"
                        >
                            <SelectTrigger id="test_type">
                                <SelectValue
                                    :placeholder="
                                        t(
                                            'cuttingTests.index.filters.testType.placeholder',
                                        )
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    {{
                                        t(
                                            'cuttingTests.index.filters.testType.options.all',
                                        )
                                    }}
                                </SelectItem>
                                <SelectItem value="final">
                                    {{
                                        t(
                                            'cuttingTests.index.filters.testType.options.final',
                                        )
                                    }}
                                </SelectItem>
                                <SelectItem value="container">
                                    {{
                                        t(
                                            'cuttingTests.index.filters.testType.options.container',
                                        )
                                    }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label for="container_id">
                            {{
                                t('cuttingTests.index.filters.container.label')
                            }}
                        </Label>
                        <Input
                            id="container_id"
                            v-model="filters.container_id"
                            :placeholder="
                                t(
                                    'cuttingTests.index.filters.container.placeholder',
                                )
                            "
                            @input="handleFilterChange"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label>
                            {{
                                t(
                                    'cuttingTests.index.filters.moistureRange.label',
                                )
                            }}
                        </Label>
                        <div class="flex gap-2">
                            <Input
                                v-model="filters.moisture_min"
                                :placeholder="
                                    t(
                                        'cuttingTests.index.filters.moistureRange.minPlaceholder',
                                    )
                                "
                                type="number"
                                step="0.1"
                                @input="handleFilterChange"
                            />
                            <Input
                                v-model="filters.moisture_max"
                                :placeholder="
                                    t(
                                        'cuttingTests.index.filters.moistureRange.maxPlaceholder',
                                    )
                                "
                                type="number"
                                step="0.1"
                                @input="handleFilterChange"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="date_from">
                            {{ t('cuttingTests.index.filters.dateFrom.label') }}
                        </Label>
                        <Input
                            id="date_from"
                            v-model="filters.date_from"
                            type="date"
                            @change="handleSearch"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_to">
                            {{ t('cuttingTests.index.filters.dateTo.label') }}
                        </Label>
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
                        {{ t('cuttingTests.index.filters.actions.search') }}
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters">
                        {{ t('cuttingTests.index.filters.actions.clear') }}
                    </Button>
                    <span class="ml-auto text-sm text-muted-foreground">
                        {{
                            t('cuttingTests.index.summary.total', {
                                count: totalTests,
                            })
                        }}
                    </span>
                </div>
            </Card>

            <Card class="flex-1 py-2">
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.bill',
                                            )
                                        }}
                                    </TableHead>
                                    <TableHead>
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.sampleOrContainer',
                                            )
                                        }}
                                    </TableHead>
                                    <TableHead class="text-right">
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.moisture',
                                            )
                                        }}
                                    </TableHead>
                                    <TableHead class="text-right">
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.defectiveNut',
                                            )
                                        }}
                                    </TableHead>
                                    <TableHead class="text-right">
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.goodKernel',
                                            )
                                        }}
                                    </TableHead>
                                    <TableHead class="text-right">
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.outturn',
                                            )
                                        }}
                                    </TableHead>
                                    <TableHead>
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.created',
                                            )
                                        }}
                                    </TableHead>
                                    <TableHead class="w-[140px] text-right">
                                        {{
                                            t(
                                                'cuttingTests.index.table.headers.actions',
                                            )
                                        }}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="cuttingTests.length === 0">
                                    <TableCell
                                        colspan="8"
                                        class="py-8 text-center text-muted-foreground"
                                    >
                                        {{ t('cuttingTests.index.empty') }}
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
                                            @click.stop="openBill(test)"
                                        >
                                            {{ getBillDisplay(test) }}
                                        </button>
                                        <div
                                            v-if="
                                                test.bill?.seller ||
                                                test.bill?.buyer
                                            "
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'cuttingTests.index.table.billParties',
                                                    {
                                                        seller:
                                                            test.bill?.seller ||
                                                            t(
                                                                'common.placeholders.notAvailable',
                                                            ),
                                                        buyer:
                                                            test.bill?.buyer ||
                                                            t(
                                                                'common.placeholders.notAvailable',
                                                            ),
                                                    },
                                                )
                                            }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            class="flex items-center gap-2 text-sm"
                                        >
                                            <template
                                                v-if="test.is_final_sample"
                                            >
                                                <span
                                                    class="rounded-md bg-muted px-2 py-0.5 font-semibold text-muted-foreground"
                                                >
                                                    {{
                                                        getFinalSampleLabel(
                                                            test.type,
                                                        )
                                                    }}
                                                </span>
                                            </template>
                                            <template v-else>
                                                <button
                                                    v-if="test.container"
                                                    type="button"
                                                    class="text-primary underline-offset-4 hover:underline"
                                                    @click.stop="
                                                        openContainer(test)
                                                    "
                                                >
                                                    {{
                                                        getContainerDisplay(
                                                            test,
                                                        )
                                                    }}
                                                </button>
                                                <span
                                                    v-else
                                                    class="text-muted-foreground"
                                                >
                                                    {{
                                                        getContainerDisplay(
                                                            test,
                                                        )
                                                    }}
                                                </span>
                                            </template>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right font-mono">
                                        <span v-if="test.moisture_formatted">
                                            {{ test.moisture_formatted }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                        >
                                            {{ placeholder }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right font-mono">
                                        <span
                                            v-if="test.defective_nut_formatted"
                                        >
                                            {{ test.defective_nut_formatted }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                        >
                                            {{ placeholder }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right font-mono">
                                        <span
                                            v-if="
                                                test.w_good_kernel !== null &&
                                                test.w_good_kernel !== undefined
                                            "
                                        >
                                            {{ test.w_good_kernel }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                        >
                                            {{ placeholder }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right font-mono">
                                        <span
                                            v-if="test.outturn_rate_formatted"
                                        >
                                            {{ test.outturn_rate_formatted }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                        >
                                            {{ placeholder }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                new Date(
                                                    test.created_at,
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            :aria-label="
                                                t('cuttingTests.index.sr.view')
                                            "
                                            @click.stop="
                                                viewCuttingTest(test.id)
                                            "
                                        >
                                            <Eye class="h-4 w-4" />
                                            <span class="sr-only">
                                                {{
                                                    t(
                                                        'cuttingTests.index.sr.view',
                                                    )
                                                }}
                                            </span>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            :aria-label="
                                                t('cuttingTests.index.sr.edit')
                                            "
                                            @click.stop="
                                                editCuttingTest(test.id)
                                            "
                                        >
                                            <Pencil class="h-4 w-4" />
                                            <span class="sr-only">
                                                {{
                                                    t(
                                                        'cuttingTests.index.sr.edit',
                                                    )
                                                }}
                                            </span>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:text-destructive"
                                            :aria-label="
                                                t(
                                                    'cuttingTests.index.sr.delete',
                                                )
                                            "
                                            @click.stop="
                                                deleteCuttingTest(test.id)
                                            "
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            <span class="sr-only">
                                                {{
                                                    t(
                                                        'cuttingTests.index.sr.delete',
                                                    )
                                                }}
                                            </span>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <div
                v-if="cuttingTests.length > 0 && pagination.last_page > 1"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    {{
                        t('cuttingTests.index.pagination.summary', {
                            from: currentFrom,
                            to: currentTo,
                            total: totalTests,
                        })
                    }}
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
                                {{
                                    t('cuttingTests.index.pagination.previous')
                                }}
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
                                {{ t('cuttingTests.index.pagination.next') }}
                            </Button>
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
