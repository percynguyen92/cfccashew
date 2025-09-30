<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import * as billRoutes from '@/routes/bills';
import * as containerRoutes from '@/routes/containers';
import { AlertTriangle, Loader2 } from 'lucide-vue-next';

interface StatsResponse {
    bills: {
        total: number;
        pending_final_tests: number;
        missing_final_samples: number;
    };
    containers: {
        high_moisture: number;
        pending_tests: number;
    };
    cutting_tests: {
        high_moisture: number;
        moisture_distribution?: unknown;
    };
}

interface BillSummary {
    id: number;
    slug?: string | null;
    bill_number?: string | null;
    seller?: string | null;
    buyer?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
}

interface CuttingTestSummary {
    id: number;
    moisture?: number | string | null;
    created_at?: string | null;
}

interface ContainerSummary {
    id: number;
    container_number?: string | null;
    truck?: string | null;
    average_moisture?: number | string | null;
    bill?: BillSummary | null;
    cutting_tests?: CuttingTestSummary[];
    created_at?: string | null;
    updated_at?: string | null;
}

interface MoistureDistributionResponse {
    total_tests: number;
    avg_moisture: number | null;
    min_moisture: number | null;
    max_moisture: number | null;
    distribution: {
        low: number;
        medium: number;
        high: number;
    };
}

interface ListResponse<T> {
    data: T[];
}

type StatsRange = 'week' | 'month' | 'year';
type RecentRange = 'week' | 'month';

type RangeOption<T extends string> = { value: T; label: string };

interface StatsCard {
    key: string;
    label: string;
    description: string;
    value: number | string | null | undefined;
}

type MoistureBand = 'low' | 'medium' | 'high';

const statsRangeOptions = computed<RangeOption<StatsRange>[]>(() => [
    { value: 'week', label: t('dashboard.ranges.week') },
    { value: 'month', label: t('dashboard.ranges.month') },
    { value: 'year', label: t('dashboard.ranges.year') },
]);

const recentRangeOptions = computed<RangeOption<RecentRange>[]>(() => [
    { value: 'week', label: t('dashboard.ranges.week') },
    { value: 'month', label: t('dashboard.ranges.month') },
]);

const { breadcrumbs } = useBreadcrumbs();
const { t } = useI18n();

const isClient = typeof window !== 'undefined';

const statsRange = ref<StatsRange>('week');
const stats = ref<StatsResponse | null>(null);
const statsLoading = ref(false);
const statsError = ref<string | null>(null);

const recentRange = ref<RecentRange>('week');
const recentBills = ref<BillSummary[]>([]);
const recentLoading = ref(false);
const recentError = ref<string | null>(null);

const billsMissingFinalSamples = ref<BillSummary[]>([]);
const billsMissingLoading = ref(false);
const billsMissingError = ref<string | null>(null);

const highMoistureContainers = ref<ContainerSummary[]>([]);
const highMoistureLoading = ref(false);
const highMoistureError = ref<string | null>(null);

const moistureDistribution = ref<MoistureDistributionResponse | null>(null);
const moistureLoading = ref(false);
const moistureError = ref<string | null>(null);

const placeholder = computed(() => t('common.placeholders.notAvailable'));
const loadingLabel = computed(() => t('common.messages.loading'));
const emptyLabel = computed(() => t('common.messages.noData'));

const statsRangeLabel = computed(() =>
    statsRangeOptions.value.find((option) => option.value === statsRange.value)?.label ?? '',
);

const recentRangeLabel = computed(() =>
    recentRangeOptions.value.find((option) => option.value === recentRange.value)?.label ?? '',
);

const statsCards = computed<StatsCard[]>(() => {
    const value = stats.value;

    return [
        {
            key: 'total_bills',
            label: t('dashboard.stats.cards.totalBills.label'),
            description: t('dashboard.stats.cards.totalBills.description'),
            value: value?.bills.total ?? null,
        },
        {
            key: 'pending_final_tests',
            label: t('dashboard.stats.cards.pendingFinalTests.label'),
            description: t('dashboard.stats.cards.pendingFinalTests.description'),
            value: value?.bills.pending_final_tests ?? null,
        },
        {
            key: 'missing_final_samples',
            label: t('dashboard.stats.cards.missingFinalSamples.label'),
            description: t('dashboard.stats.cards.missingFinalSamples.description'),
            value: value?.bills.missing_final_samples ?? null,
        },
        {
            key: 'high_moisture_containers',
            label: t('dashboard.stats.cards.highMoistureContainers.label'),
            description: t('dashboard.stats.cards.highMoistureContainers.description'),
            value: value?.containers.high_moisture ?? null,
        },
        {
            key: 'pending_container_tests',
            label: t('dashboard.stats.cards.pendingContainerTests.label'),
            description: t('dashboard.stats.cards.pendingContainerTests.description'),
            value: value?.containers.pending_tests ?? null,
        },
        {
            key: 'high_moisture_tests',
            label: t('dashboard.stats.cards.highMoistureTests.label'),
            description: t('dashboard.stats.cards.highMoistureTests.description'),
            value: value?.cutting_tests.high_moisture ?? null,
        },
    ];
});

const dateFormatter = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
});

const numberFormatter = new Intl.NumberFormat();

const toNumber = (value: number | string | null | undefined): number | null => {
    if (value === null || value === undefined) {
        return null;
    }

    if (typeof value === 'number' && Number.isFinite(value)) {
        return value;
    }

    const parsed = Number.parseFloat(String(value));
    return Number.isFinite(parsed) ? parsed : null;
};

const formatCount = (value: number | string | null | undefined): string => {
    const numeric = toNumber(value);
    if (numeric === null) {
        return placeholder.value;
    }

    return numberFormatter.format(numeric);
};

const formatMoisture = (value: number | string | null | undefined): string => {
    const numeric = toNumber(value);
    if (numeric === null) {
        return placeholder.value;
    }

    return `${numeric.toFixed(1)}%`;
};

const formatDate = (value: string | null | undefined): string => {
    if (!value) {
        return placeholder.value;
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return placeholder.value;
    }

    return dateFormatter.format(parsed);
};

const highestMoistureForContainer = (container: ContainerSummary): number | null => {
    if (!container.cutting_tests || container.cutting_tests.length === 0) {
        return container.average_moisture ? toNumber(container.average_moisture) : null;
    }

    return container.cutting_tests.reduce<number | null>((current, test) => {
        const moisture = toNumber(test.moisture);

        if (moisture === null) {
            return current;
        }

        if (current === null || moisture > current) {
            return moisture;
        }

        return current;
    }, null);
};

const resolveBillUrl = (bill: BillSummary): string =>
    billRoutes.show.url(bill.slug ?? bill.id);

const resolveBillLabel = (bill: BillSummary): string => {
    const number = bill.bill_number?.trim();
    return number && number.length > 0 ? number : `#${bill.id}`;
};

const resolveContainerUrl = (container: ContainerSummary): string =>
    containerRoutes.show.url(container.container_number ?? container.id);

const resolveContainerLabel = (container: ContainerSummary): string => {
    const number = container.container_number?.trim();
    return number && number.length > 0 ? number : `#${container.id}`;
};

const moistureSegments = computed(() => {
    const distribution = moistureDistribution.value?.distribution;
    const bands: MoistureBand[] = ['low', 'medium', 'high'];

    return bands.map((band) => ({
        key: band,
        label: t(`dashboard.moistureDistribution.segments.${band}`),
        count: distribution?.[band] ?? 0,
    }));
});

const totalDistributionCount = computed(() => moistureDistribution.value?.total_tests ?? 0);

const formatDistributionPercentage = (count: number): string => {
    const total = totalDistributionCount.value;

    if (!total) {
        return '0%';
    }

    const percentage = (count / total) * 100;
    return `${Math.round(percentage)}%`;
};

const buildDashboardUrl = (path: string) => `/api/dashboard${path}`;

const apiGet = async <T>(endpoint: string, params?: Record<string, string | number | undefined>): Promise<T> => {
    if (!isClient) {
        throw new Error('Dashboard data requests are only available in the browser.');
    }

    const url = new URL(endpoint, window.location.origin);

    if (params) {
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.set(key, String(value));
            }
        });
    }

    const response = await fetch(url.toString(), {
        headers: {
            Accept: 'application/json',
        },
        credentials: 'same-origin',
    });

    let payload: unknown = null;

    try {
        payload = await response.json();
    } catch {
        payload = null;
    }

    if (!response.ok) {
        const message = (payload as { message?: string } | null)?.message ?? 'Request failed';
        throw new Error(message);
    }

    return payload as T;
};

const fetchStats = async () => {
    if (!isClient) {
        return;
    }

    statsLoading.value = true;
    statsError.value = null;

    try {
        stats.value = await apiGet<StatsResponse>(
            buildDashboardUrl('/stats'),
            { range: statsRange.value },
        );
    } catch (error) {
        statsError.value = error instanceof Error ? error.message : String(error);
    } finally {
        statsLoading.value = false;
    }
};

const fetchRecentBills = async () => {
    if (!isClient) {
        return;
    }

    recentLoading.value = true;
    recentError.value = null;

    try {
        const response = await apiGet<ListResponse<BillSummary>>(
            buildDashboardUrl('/recent-bills'),
            { range: recentRange.value },
        );

        recentBills.value = response.data;
    } catch (error) {
        recentError.value = error instanceof Error ? error.message : String(error);
    } finally {
        recentLoading.value = false;
    }
};

const fetchBillsMissingFinalSamples = async () => {
    if (!isClient) {
        return;
    }

    billsMissingLoading.value = true;
    billsMissingError.value = null;

    try {
        const response = await apiGet<ListResponse<BillSummary>>(
            buildDashboardUrl('/bills-missing-final-samples'),
        );

        billsMissingFinalSamples.value = response.data;
    } catch (error) {
        billsMissingError.value = error instanceof Error ? error.message : String(error);
    } finally {
        billsMissingLoading.value = false;
    }
};

const fetchHighMoistureContainers = async () => {
    if (!isClient) {
        return;
    }

    highMoistureLoading.value = true;
    highMoistureError.value = null;

    try {
        const response = await apiGet<ListResponse<ContainerSummary>>(
            buildDashboardUrl('/containers-high-moisture'),
        );

        highMoistureContainers.value = response.data;
    } catch (error) {
        highMoistureError.value = error instanceof Error ? error.message : String(error);
    } finally {
        highMoistureLoading.value = false;
    }
};

const fetchMoistureDistribution = async () => {
    if (!isClient) {
        return;
    }

    moistureLoading.value = true;
    moistureError.value = null;

    try {
        moistureDistribution.value = await apiGet<MoistureDistributionResponse>(
            buildDashboardUrl('/moisture-distribution'),
        );
    } catch (error) {
        moistureError.value = error instanceof Error ? error.message : String(error);
    } finally {
        moistureLoading.value = false;
    }
};

watch(
    statsRange,
    () => {
        if (!isClient) {
            return;
        }

        void fetchStats();
    },
    { immediate: true },
);

watch(
    recentRange,
    () => {
        if (!isClient) {
            return;
        }

        void fetchRecentBills();
    },
    { immediate: true },
);

if (isClient) {
    void fetchBillsMissingFinalSamples();
    void fetchHighMoistureContainers();
    void fetchMoistureDistribution();
}

</script>

<template>
    <Head :title="t('dashboard.headTitle')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 overflow-x-hidden p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">
                        {{ t('dashboard.title') }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ t('dashboard.description') }}
                    </p>
                </div>
            </div>

            <Card>
                <CardHeader class="gap-4 sm:flex sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <CardTitle>{{ t('dashboard.stats.title') }}</CardTitle>
                        <CardDescription>
                            {{ t('dashboard.stats.description', { range: statsRangeLabel || t('dashboard.ranges.default') }) }}
                        </CardDescription>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                        <label class="text-sm font-medium text-muted-foreground sm:mr-2">
                            {{ t('dashboard.stats.rangeLabel') }}
                        </label>
                        <Select v-model="statsRange">
                            <SelectTrigger class="w-full sm:w-48">
                                <SelectValue :placeholder="t('dashboard.ranges.placeholder')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in statsRangeOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="statsLoading && !stats" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <Skeleton v-for="index in 6" :key="index" class="h-24 w-full" />
                    </div>
                    <div
                        v-else-if="statsError && !stats"
                        class="flex flex-col gap-3 rounded-md border border-destructive/20 bg-destructive/5 p-4"
                    >
                        <div class="flex items-start gap-2 text-sm text-destructive">
                            <AlertTriangle class="mt-0.5 h-4 w-4" />
                            <span>{{ statsError }}</span>
                        </div>
                        <Button variant="outline" size="sm" @click="fetchStats">
                            {{ t('common.actions.retry') }}
                        </Button>
                    </div>
                    <div v-else-if="stats" class="space-y-4">
                        <div class="relative">
                            <div
                                v-if="statsLoading"
                                class="absolute right-0 top-0 flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Loader2 class="h-3.5 w-3.5 animate-spin" />
                                <span>{{ loadingLabel }}</span>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                <div
                                    v-for="card in statsCards"
                                    :key="card.key"
                                    class="rounded-lg border bg-card/60 p-4"
                                >
                                    <div class="text-sm font-medium text-muted-foreground">
                                        {{ card.label }}
                                    </div>
                                    <div class="mt-2 text-2xl font-semibold">
                                        {{ formatCount(card.value) }}
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ card.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="statsError"
                            class="flex items-start gap-2 rounded-md border border-destructive/20 bg-destructive/5 p-3 text-xs text-destructive"
                        >
                            <AlertTriangle class="mt-0.5 h-3.5 w-3.5" />
                            <span>{{ t('dashboard.messages.refreshFailed') }}</span>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">
                        {{ emptyLabel }}
                    </div>
                </CardContent>
            </Card>
            <div class="grid gap-6 xl:grid-cols-2">
                <Card>
                    <CardHeader class="gap-4 sm:flex sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <CardTitle>{{ t('dashboard.recentBills.title') }}</CardTitle>
                            <CardDescription>
                                {{ t('dashboard.recentBills.description', { range: recentRangeLabel || t('dashboard.ranges.default') }) }}
                            </CardDescription>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                            <label class="text-sm font-medium text-muted-foreground sm:mr-2">
                                {{ t('dashboard.recentBills.rangeLabel') }}
                            </label>
                            <Select v-model="recentRange">
                                <SelectTrigger class="w-full sm:w-48">
                                    <SelectValue :placeholder="t('dashboard.ranges.placeholder')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in recentRangeOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentLoading && recentBills.length === 0" class="space-y-3">
                            <Skeleton v-for="index in 3" :key="`recent-${index}`" class="h-12 w-full" />
                        </div>
                        <div
                            v-else-if="recentError && recentBills.length === 0"
                            class="flex flex-col gap-3 rounded-md border border-destructive/20 bg-destructive/5 p-4"
                        >
                            <div class="flex items-start gap-2 text-sm text-destructive">
                                <AlertTriangle class="mt-0.5 h-4 w-4" />
                                <span>{{ recentError }}</span>
                            </div>
                            <Button variant="outline" size="sm" @click="fetchRecentBills">
                                {{ t('common.actions.retry') }}
                            </Button>
                        </div>
                        <div v-else-if="recentBills.length === 0" class="text-sm text-muted-foreground">
                            {{ t('dashboard.recentBills.empty') }}
                        </div>
                        <div v-else class="relative">
                            <div
                                v-if="recentLoading"
                                class="absolute right-0 top-0 flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Loader2 class="h-3.5 w-3.5 animate-spin" />
                                <span>{{ loadingLabel }}</span>
                            </div>
                            <div class="overflow-x-auto">
                                <Table class="min-w-full">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{{ t('dashboard.recentBills.table.bill') }}</TableHead>
                                            <TableHead>{{ t('dashboard.recentBills.table.seller') }}</TableHead>
                                            <TableHead>{{ t('dashboard.recentBills.table.buyer') }}</TableHead>
                                            <TableHead>{{ t('dashboard.recentBills.table.createdAt') }}</TableHead>
                                            <TableHead class="text-right">
                                                {{ t('dashboard.recentBills.table.actions') }}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="bill in recentBills" :key="bill.id">
                                            <TableCell>
                                                <Link :href="resolveBillUrl(bill)" class="font-medium hover:underline">
                                                    {{ resolveBillLabel(bill) }}
                                                </Link>
                                            </TableCell>
                                            <TableCell>{{ bill.seller || placeholder }}</TableCell>
                                            <TableCell>{{ bill.buyer || placeholder }}</TableCell>
                                            <TableCell>{{ formatDate(bill.created_at) }}</TableCell>
                                            <TableCell class="text-right">
                                                <Link
                                                    :href="resolveBillUrl(bill)"
                                                    class="text-sm font-medium text-primary hover:underline"
                                                >
                                                    {{ t('common.actions.view') }}
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div
                                v-if="recentError"
                                class="mt-3 rounded-md border border-destructive/20 bg-destructive/5 p-3 text-xs text-destructive"
                            >
                                <div class="flex items-start gap-2">
                                    <AlertTriangle class="mt-0.5 h-3.5 w-3.5" />
                                    <span>{{ t('dashboard.messages.refreshFailed') }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('dashboard.billsMissingFinalSamples.title') }}</CardTitle>
                        <CardDescription>
                            {{ t('dashboard.billsMissingFinalSamples.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="billsMissingLoading && billsMissingFinalSamples.length === 0" class="space-y-3">
                            <Skeleton v-for="index in 3" :key="`missing-${index}`" class="h-12 w-full" />
                        </div>
                        <div
                            v-else-if="billsMissingError && billsMissingFinalSamples.length === 0"
                            class="flex flex-col gap-3 rounded-md border border-destructive/20 bg-destructive/5 p-4"
                        >
                            <div class="flex items-start gap-2 text-sm text-destructive">
                                <AlertTriangle class="mt-0.5 h-4 w-4" />
                                <span>{{ billsMissingError }}</span>
                            </div>
                            <Button variant="outline" size="sm" @click="fetchBillsMissingFinalSamples">
                                {{ t('common.actions.retry') }}
                            </Button>
                        </div>
                        <div
                            v-else-if="billsMissingFinalSamples.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            {{ t('dashboard.billsMissingFinalSamples.empty') }}
                        </div>
                        <div v-else class="relative">
                            <div
                                v-if="billsMissingLoading"
                                class="absolute right-0 top-0 flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Loader2 class="h-3.5 w-3.5 animate-spin" />
                                <span>{{ loadingLabel }}</span>
                            </div>
                            <div class="overflow-x-auto">
                                <Table class="min-w-full">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{{ t('dashboard.billsMissingFinalSamples.table.bill') }}</TableHead>
                                            <TableHead>{{ t('dashboard.billsMissingFinalSamples.table.seller') }}</TableHead>
                                            <TableHead>{{ t('dashboard.billsMissingFinalSamples.table.updatedAt') }}</TableHead>
                                            <TableHead class="text-right">
                                                {{ t('dashboard.billsMissingFinalSamples.table.actions') }}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="bill in billsMissingFinalSamples" :key="bill.id">
                                            <TableCell>
                                                <Link :href="resolveBillUrl(bill)" class="font-medium hover:underline">
                                                    {{ resolveBillLabel(bill) }}
                                                </Link>
                                            </TableCell>
                                            <TableCell>{{ bill.seller || placeholder }}</TableCell>
                                            <TableCell>{{ formatDate(bill.updated_at) }}</TableCell>
                                            <TableCell class="text-right">
                                                <Link
                                                    :href="resolveBillUrl(bill)"
                                                    class="text-sm font-medium text-primary hover:underline"
                                                >
                                                    {{ t('common.actions.view') }}
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div
                                v-if="billsMissingError"
                                class="mt-3 rounded-md border border-destructive/20 bg-destructive/5 p-3 text-xs text-destructive"
                            >
                                <div class="flex items-start gap-2">
                                    <AlertTriangle class="mt-0.5 h-3.5 w-3.5" />
                                    <span>{{ t('dashboard.messages.refreshFailed') }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            <div class="grid gap-6 xl:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('dashboard.highMoistureContainers.title') }}</CardTitle>
                        <CardDescription>
                            {{ t('dashboard.highMoistureContainers.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="highMoistureLoading && highMoistureContainers.length === 0" class="space-y-3">
                            <Skeleton v-for="index in 3" :key="`container-${index}`" class="h-12 w-full" />
                        </div>
                        <div
                            v-else-if="highMoistureError && highMoistureContainers.length === 0"
                            class="flex flex-col gap-3 rounded-md border border-destructive/20 bg-destructive/5 p-4"
                        >
                            <div class="flex items-start gap-2 text-sm text-destructive">
                                <AlertTriangle class="mt-0.5 h-4 w-4" />
                                <span>{{ highMoistureError }}</span>
                            </div>
                            <Button variant="outline" size="sm" @click="fetchHighMoistureContainers">
                                {{ t('common.actions.retry') }}
                            </Button>
                        </div>
                        <div
                            v-else-if="highMoistureContainers.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            {{ t('dashboard.highMoistureContainers.empty') }}
                        </div>
                        <div v-else class="relative">
                            <div
                                v-if="highMoistureLoading"
                                class="absolute right-0 top-0 flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Loader2 class="h-3.5 w-3.5 animate-spin" />
                                <span>{{ loadingLabel }}</span>
                            </div>
                            <div class="overflow-x-auto">
                                <Table class="min-w-full">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{{ t('dashboard.highMoistureContainers.table.container') }}</TableHead>
                                            <TableHead>{{ t('dashboard.highMoistureContainers.table.bill') }}</TableHead>
                                            <TableHead>{{ t('dashboard.highMoistureContainers.table.moisture') }}</TableHead>
                                            <TableHead>{{ t('dashboard.highMoistureContainers.table.updatedAt') }}</TableHead>
                                            <TableHead class="text-right">
                                                {{ t('dashboard.highMoistureContainers.table.actions') }}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="container in highMoistureContainers" :key="container.id">
                                            <TableCell>
                                                <Link
                                                    :href="resolveContainerUrl(container)"
                                                    class="font-medium hover:underline"
                                                >
                                                    {{ resolveContainerLabel(container) }}
                                                </Link>
                                            </TableCell>
                                            <TableCell>
                                                <span v-if="container.bill">
                                                    <Link
                                                        :href="resolveBillUrl(container.bill)"
                                                        class="hover:underline"
                                                    >
                                                        {{ resolveBillLabel(container.bill) }}
                                                    </Link>
                                                </span>
                                                <span v-else>
                                                    {{ placeholder }}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                {{ formatMoisture(highestMoistureForContainer(container)) }}
                                            </TableCell>
                                            <TableCell>{{ formatDate(container.updated_at ?? container.created_at) }}</TableCell>
                                            <TableCell class="text-right">
                                                <Link
                                                    :href="resolveContainerUrl(container)"
                                                    class="text-sm font-medium text-primary hover:underline"
                                                >
                                                    {{ t('common.actions.view') }}
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div
                                v-if="highMoistureError"
                                class="mt-3 rounded-md border border-destructive/20 bg-destructive/5 p-3 text-xs text-destructive"
                            >
                                <div class="flex items-start gap-2">
                                    <AlertTriangle class="mt-0.5 h-3.5 w-3.5" />
                                    <span>{{ t('dashboard.messages.refreshFailed') }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('dashboard.moistureDistribution.title') }}</CardTitle>
                        <CardDescription>
                            {{ t('dashboard.moistureDistribution.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="moistureLoading && !moistureDistribution" class="space-y-3">
                            <Skeleton v-for="index in 3" :key="`distribution-${index}`" class="h-12 w-full" />
                        </div>
                        <div
                            v-else-if="moistureError && !moistureDistribution"
                            class="flex flex-col gap-3 rounded-md border border-destructive/20 bg-destructive/5 p-4"
                        >
                            <div class="flex items-start gap-2 text-sm text-destructive">
                                <AlertTriangle class="mt-0.5 h-4 w-4" />
                                <span>{{ moistureError }}</span>
                            </div>
                            <Button variant="outline" size="sm" @click="fetchMoistureDistribution">
                                {{ t('common.actions.retry') }}
                            </Button>
                        </div>
                        <div v-else-if="!moistureDistribution" class="text-sm text-muted-foreground">
                            {{ t('dashboard.moistureDistribution.empty') }}
                        </div>
                        <div v-else class="space-y-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-lg border bg-card/60 p-4">
                                    <div class="text-sm font-medium text-muted-foreground">
                                        {{ t('dashboard.moistureDistribution.metrics.avg') }}
                                    </div>
                                    <div class="mt-2 text-2xl font-semibold">
                                        {{ formatMoisture(moistureDistribution.avg_moisture) }}
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ t('dashboard.moistureDistribution.metrics.sampleSize', { total: formatCount(moistureDistribution.total_tests) }) }}
                                    </p>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-lg border bg-card/60 p-4">
                                        <div class="text-sm font-medium text-muted-foreground">
                                            {{ t('dashboard.moistureDistribution.metrics.min') }}
                                        </div>
                                        <div class="mt-2 text-xl font-semibold">
                                            {{ formatMoisture(moistureDistribution.min_moisture) }}
                                        </div>
                                    </div>
                                    <div class="rounded-lg border bg-card/60 p-4">
                                        <div class="text-sm font-medium text-muted-foreground">
                                            {{ t('dashboard.moistureDistribution.metrics.max') }}
                                        </div>
                                        <div class="mt-2 text-xl font-semibold">
                                            {{ formatMoisture(moistureDistribution.max_moisture) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground">
                                    {{ t('dashboard.moistureDistribution.segments.title') }}
                                </h4>
                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                    <div
                                        v-for="segment in moistureSegments"
                                        :key="segment.key"
                                        class="rounded-lg border bg-muted/30 p-4"
                                    >
                                        <div class="text-sm font-medium text-muted-foreground">
                                            {{ segment.label }}
                                        </div>
                                        <div class="mt-2 text-xl font-semibold">
                                            {{ formatCount(segment.count) }}
                                        </div>
                                        <div class="mt-1 text-xs text-muted-foreground">
                                            {{ formatDistributionPercentage(segment.count) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="moistureError"
                                class="rounded-md border border-destructive/20 bg-destructive/5 p-3 text-xs text-destructive"
                            >
                                <div class="flex items-start gap-2">
                                    <AlertTriangle class="mt-0.5 h-3.5 w-3.5" />
                                    <span>{{ t('dashboard.messages.refreshFailed') }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
