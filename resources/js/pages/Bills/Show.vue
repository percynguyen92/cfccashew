<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import ContainerTable from '@/components/ContainerTable.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

import CuttingTestForm from '@/components/cutting-tests/CuttingTestForm.vue';
import CuttingTestTable from '@/components/CuttingTestTable.vue';
import type { Bill, Container, CuttingTest } from '@/types';
import { Package, Plus, TestTube } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Props {
    bill: Bill;
}

const props = defineProps<Props>();
const bill = computed(() => props.bill);

const isEditDialogOpen = ref(false);

const openEditDialog = () => {
    isEditDialogOpen.value = true;
};

const closeEditDialog = () => {
    isEditDialogOpen.value = false;
};

const handleEditSuccess = () => {
    closeEditDialog();
};

const handleEditCancel = () => {
    closeEditDialog();
};

const containers = computed(() => {
    const raw = bill.value.containers as
        | Container[]
        | { data?: Container[]; [key: string]: unknown }
        | undefined;

    if (Array.isArray(raw)) {
        return raw.filter((container): container is Container =>
            Boolean(container),
        );
    }

    if (raw && typeof raw === 'object') {
        const data = (raw as { data?: Container[] }).data;

        if (Array.isArray(data)) {
            return data.filter((container): container is Container =>
                Boolean(container),
            );
        }
    }

    return [] as Container[];
});

type CuttingTestCollection =
    | CuttingTest[]
    | { data?: CuttingTest[]; [key: string]: unknown }
    | null
    | undefined;

const normalizeCuttingTests = (tests: CuttingTestCollection): CuttingTest[] => {
    if (Array.isArray(tests)) {
        return tests.filter((test): test is CuttingTest => Boolean(test));
    }

    if (tests && typeof tests === 'object') {
        const data = (tests as { data?: CuttingTest[] }).data;

        if (Array.isArray(data)) {
            return data.filter((test): test is CuttingTest => Boolean(test));
        }
    }

    return [];
};

const sortCuttingTests = (tests: CuttingTest[]): CuttingTest[] =>
    [...tests].sort(
        (a, b) =>
            new Date(b.created_at).getTime() - new Date(a.created_at).getTime(),
    );

const resolveCuttingTests = (tests: CuttingTestCollection): CuttingTest[] =>
    sortCuttingTests(normalizeCuttingTests(tests));

const resolveContainerTests = (tests: CuttingTestCollection): CuttingTest[] =>
    resolveCuttingTests(tests);

const finalSamples = computed(() =>
    resolveCuttingTests(bill.value.final_samples as CuttingTestCollection),
);

const isCuttingTestDialogOpen = ref(false);
const cuttingTestDialogMode = ref<'create' | 'edit'>('create');
const cuttingTestBeingEdited = ref<CuttingTest | null>(null);
const defaultFinalSampleType = ref<1 | 2 | 3>(1);

const finalSampleTypesUsed = computed(() => {
    const used = new Set<1 | 2 | 3>();

    finalSamples.value.forEach((test) => {
        if (test.type === 1 || test.type === 2 || test.type === 3) {
            used.add(test.type);
        }
    });

    return used;
});

const nextFinalSampleType = computed<1 | 2 | 3>(() => {
    for (const type of [1, 2, 3] as const) {
        if (!finalSampleTypesUsed.value.has(type)) {
            return type;
        }
    }

    return 1;
});

const openCreateCuttingTestDialog = () => {
    cuttingTestDialogMode.value = 'create';
    cuttingTestBeingEdited.value = null;
    defaultFinalSampleType.value = nextFinalSampleType.value;
    isCuttingTestDialogOpen.value = true;
};

const openEditCuttingTestDialog = (test: CuttingTest) => {
    cuttingTestDialogMode.value = 'edit';
    cuttingTestBeingEdited.value = test;
    if (test.type === 1 || test.type === 2 || test.type === 3) {
        defaultFinalSampleType.value = test.type;
    }
    isCuttingTestDialogOpen.value = true;
};

const closeCuttingTestDialog = () => {
    isCuttingTestDialogOpen.value = false;
};

const handleCuttingTestSuccess = () => {
    closeCuttingTestDialog();
};

const handleCuttingTestCancel = () => {
    closeCuttingTestDialog();
};

watch(isCuttingTestDialogOpen, (isOpen) => {
    if (!isOpen) {
        cuttingTestDialogMode.value = 'create';
        cuttingTestBeingEdited.value = null;
    }
});

const { breadcrumbs } = useBreadcrumbs();

const formatOutturn = (outturn: number | string | null | undefined): string => {
    if (outturn === null || outturn === undefined) return '-';

    const numeric =
        typeof outturn === 'number'
            ? outturn
            : Number.parseFloat(outturn as string);

    if (Number.isNaN(numeric)) {
        return '-';
    }

    return `${numeric.toFixed(2)} lbs/80kg`;
};
</script>

<template>
    <Head :title="`Bill #${bill.bill_number || bill.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-wrap items-center justify-between gap-4 md:flex-nowrap"
            >
                <div class="flex flex-col gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold tracking-tight">
                            Bill #{{ bill.bill_number || bill.id }}
                        </h1>
                        <Button
                            variant="outline"
                            type="button"
                            @click="openEditDialog"
                        >
                            Edit Bill
                        </Button>
                    </div>
                    <p class="text-muted-foreground">
                        Created
                        {{ new Date(bill.created_at).toLocaleDateString() }}
                    </p>
                </div>
            </div>

            <!-- Bill Information Card -->
            <Card>
                <CardContent>
                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                    >
                        <div>
                            <label
                                class="text-sm font-medium text-muted-foreground"
                                >Bill Number</label
                            >
                            <p class="text-lg font-semibold">
                                {{ bill.bill_number || '-' }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="text-sm font-medium text-muted-foreground"
                                >Seller</label
                            >
                            <p class="text-lg font-semibold">
                                {{ bill.seller || '-' }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="text-sm font-medium text-muted-foreground"
                                >Buyer</label
                            >
                            <p class="text-lg font-semibold">
                                {{ bill.buyer || '-' }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="text-sm font-medium text-muted-foreground"
                                >Average Outturn</label
                            >
                            <p class="text-lg font-semibold">
                                {{ formatOutturn(bill.average_outurn) }}
                            </p>
                        </div>
                    </div>
                    <div v-if="bill.note" class="mt-4">
                        <label class="text-sm font-medium text-muted-foreground"
                            >Note</label
                        >
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
                            <CardDescription
                                >Final sample cutting tests for this
                                bill</CardDescription
                            >
                        </div>
                        <Button size="sm" variant="outline" @click="openCreateCuttingTestDialog">
                            <Plus class="mr-1 h-4 w-4" />
                            Add Cutting Test
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="finalSamples.length > 0">
                        <CuttingTestTable
                            :tests="finalSamples"
                            @edit="openEditCuttingTestDialog"
                        />
                    </div>
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <TestTube class="mx-auto mb-4 h-12 w-12 opacity-50" />
                        <p>No final samples recorded yet</p>
                        <p class="text-sm">
                            Add final sample cutting tests to track quality
                            metrics
                        </p>
                        <Button
                            size="sm"
                            variant="outline"
                            class="mt-4"
                            @click="openCreateCuttingTestDialog"
                        >
                            <Plus class="mr-1 h-4 w-4" />
                            Add Cutting Test
                        </Button>
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
                            <CardDescription
                                >Containers associated with this
                                bill</CardDescription
                            >
                        </div>
                        <Button as-child>
                            <Link
                                :href="`/containers/create?bill_id=${bill.id}`"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Add Container
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="containers.length > 0">
                        <ContainerTable :containers="containers" />
                    </div>
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <Package class="mx-auto mb-4 h-12 w-12 opacity-50" />
                        <p>No containers added yet</p>
                        <p class="text-sm">
                            Add containers to track weights and cutting tests
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent
                class="max-h-[90vh] w-full max-w-4xl sm:max-w-4xl lg:max-w-5xl overflow-y-auto"
            >
                <BillForm
                    v-if="isEditDialogOpen"
                    :bill="bill"
                    :is-editing="true"
                    @success="handleEditSuccess"
                    @cancel="handleEditCancel"
                />
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isCuttingTestDialogOpen">
            <DialogContent
                class="max-h-[90vh] w-full max-w-5xl sm:max-w-5xl xl:max-w-6xl overflow-y-auto"
            >
                <CuttingTestForm
                    v-if="isCuttingTestDialogOpen"
                    :bill-id="bill.id"
                    :bill="bill"
                    :cutting-test="cuttingTestBeingEdited || undefined"
                    :default-type="defaultFinalSampleType"
                    @success="handleCuttingTestSuccess"
                    @cancel="handleCuttingTestCancel"
                />
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
