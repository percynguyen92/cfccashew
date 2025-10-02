<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import ContainerForm from '@/components/containers/ContainerForm.vue';
import ContainerTable from '@/components/containers/ContainerTable.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

import CuttingTestForm from '@/components/cutting-tests/CuttingTestForm.vue';
import CuttingTestTable from '@/components/cutting-tests/CuttingTestTable.vue';
import * as containerRoutes from '@/routes/containers';
import * as cuttingTestRoutes from '@/routes/cutting-tests';
import type { Bill, Container, CuttingTest } from '@/types';
import { Loader2, Package, Plus, TestTube, Pencil } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    bill: Bill;
}

const props = defineProps<Props>();
const { t } = useI18n();
const bill = computed(() => props.bill);
const billIdentifier = computed(
    () => bill.value.bill_number || String(bill.value.id),
);

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
        | { data?: Container[];[key: string]: unknown }
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
    | { data?: CuttingTest[];[key: string]: unknown }
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

const isContainerDialogOpen = ref(false);
const editingContainer = ref<Container | null>(null);

const openContainerDialog = () => {
    editingContainer.value = null;
    isContainerDialogOpen.value = true;
};

const closeContainerDialog = () => {
    isContainerDialogOpen.value = false;
    editingContainer.value = null;
};

const handleContainerSuccess = () => {
    closeContainerDialog();
};

const handleContainerCancel = () => {
    closeContainerDialog();
};

watch(isContainerDialogOpen, (isOpen) => {
    if (!isOpen) {
        editingContainer.value = null;
    }
});

type DeleteContext =
    | { type: 'container'; record: Container }
    | { type: 'cutting-test'; record: CuttingTest };

const isDeleteDialogOpen = ref(false);
const deleteContext = ref<DeleteContext | null>(null);
const isDeleting = ref(false);
const deletingContainerId = ref<number | null>(null);
const deletingCuttingTestId = ref<number | null>(null);

const openDeleteContainerDialog = (container: Container) => {
    if (!container.id) {
        return;
    }

    deleteContext.value = { type: 'container', record: container };
    isDeleteDialogOpen.value = true;
};

const openDeleteCuttingTestDialog = (test: CuttingTest) => {
    if (!test.id) {
        return;
    }

    deleteContext.value = { type: 'cutting-test', record: test };
    isDeleteDialogOpen.value = true;
};

const closeDeleteDialog = () => {
    isDeleteDialogOpen.value = false;
};

watch(isDeleteDialogOpen, (isOpen) => {
    if (!isOpen) {
        deleteContext.value = null;
        deletingContainerId.value = null;
        deletingCuttingTestId.value = null;
        isDeleting.value = false;
    }
});

const deleteDialogTitle = computed(() => {
    if (!deleteContext.value) {
        return '';
    }

    return deleteContext.value.type === 'container'
        ? t('bills.show.containers.delete.title')
        : t('bills.show.finalSamples.delete.title');
});

const deleteDialogDescription = computed(() => {
    if (!deleteContext.value) {
        return '';
    }

    if (deleteContext.value.type === 'container') {
        const container = deleteContext.value.record;
        const label = container.container_number || `#${container.id}`;
        return t('bills.show.containers.delete.description', { label });
    }

    return t('bills.show.finalSamples.delete.description');
});

const confirmDelete = () => {
    if (!deleteContext.value || isDeleting.value) {
        return;
    }

    const { type, record } = deleteContext.value;

    if (!record.id) {
        closeDeleteDialog();
        return;
    }

    const id = record.id;

    const options = {
        preserveScroll: true,
        onStart: () => {
            isDeleting.value = true;
            if (type === 'container') {
                deletingContainerId.value = id;
                deletingCuttingTestId.value = null;
            } else {
                deletingCuttingTestId.value = id;
                deletingContainerId.value = null;
            }
        },
        onSuccess: () => {
            closeDeleteDialog();
        },
        onError: () => {
            isDeleting.value = false;
            deletingContainerId.value = null;
            deletingCuttingTestId.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
            deletingContainerId.value = null;
            deletingCuttingTestId.value = null;
        },
    } as const;

    if (type === 'container') {
        router.delete(containerRoutes.destroy.url(id.toString()), options);
        return;
    }

    router.delete(cuttingTestRoutes.destroy.url(id.toString()), options);
};

const handleEditContainer = (container: Container) => {
    editingContainer.value = container;
    isContainerDialogOpen.value = true;
};

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
    if (outturn === null || outturn === undefined) {
        return t('common.placeholders.notAvailable');
    }

    const numeric =
        typeof outturn === 'number'
            ? outturn
            : Number.parseFloat(outturn as string);

    if (Number.isNaN(numeric)) {
        return t('common.placeholders.notAvailable');
    }

    return t('bills.show.outturnValue', {
        value: numeric.toFixed(2),
    });
};
</script>

<template>

    <Head :title="t('bills.show.title', { identifier: billIdentifier })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

            <!-- Bill Information & Final Samples Section -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Bill Information Card -->
                <Card>
                    <CardContent class="px-6">
                        <!-- Header Row -->
                        <div class="mb-3 flex items-center justify-between border-b pb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold">{{ t('bills.show.labels.bill') }}:</span>
                                <span class="text-lg font-bold">
                                    {{ bill.bill_number || t('common.placeholders.notAvailable') }}
                                </span>
                                <Button variant="outline" type="button" @click="openEditDialog">
                                    <Pencil class="h-3 w-3" />
                                    {{ t('bills.show.actions.edit') }}
                                </Button>
                            </div>
                            <div class="text-sm text-muted-foreground">
                                {{ t('bills.show.labels.createdAt', {
                                    date: new
                                        Date(bill.created_at).toLocaleDateString()
                                }) }}
                            </div>
                        </div>

                        <!-- Information Grid -->
                        <div class="space-y-2">
                            <!-- Row 1: Seller & Sampling Ratio -->
                            <div class="grid grid-cols-1 gap-x-6 gap-y-1 md:grid-cols-2">
                                <div class="flex gap-2">
                                    <span class="font-light">{{ t('bills.form.fields.seller.label') }}: </span>
                                    <span class="font-semibold">{{ bill.seller || t('common.placeholders.notAvailable')
                                        }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <span class="font-light">{{ t('bills.form.fields.samplingRatio.label') }}:</span>
                                    <span class="font-semibold">{{ bill.sampling_ratio }}%</span>
                                </div>
                            </div>

                            <!-- Row 2: Buyer & Jute Bag Weight -->
                            <div class="grid grid-cols-1 gap-x-6 gap-y-1 md:grid-cols-2">
                                <div class="flex gap-2">
                                    <span class="font-light">{{ t('bills.form.fields.buyer.label') }}: </span>
                                    <span class="font-semibold">{{ bill.buyer || t('common.placeholders.notAvailable')
                                        }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <span class="font-light">{{ t('bills.form.fields.wJuteBag.label') }}:</span>
                                    <span class="font-semibold">{{ bill.w_jute_bag }} kg</span>
                                </div>
                            </div>

                            <!-- Row 3: Origin -->
                            <div class="grid grid-cols-1 gap-x-6 gap-y-1 md:grid-cols-2">
                                <div class="flex gap-2">
                                    <span class="font-light">{{ t('bills.form.fields.origin.label') }}: </span>
                                    <span class="font-semibold">{{ bill.origin || t('common.placeholders.notAvailable')
                                        }}</span>
                                </div>
                            </div>

                            <!-- Separator -->
                            <div class="border-t pt-2 mt-3">
                                <!-- Row 4: Bags & Dunage & Dribag -->
                                <div class="grid grid-cols-1 gap-x-6 gap-y-1 md:grid-cols-2 mb-1">
                                    <div class="flex gap-2">
                                        <span class="font-light">{{ t('bills.form.fields.quantityOfBagsOnBl.label') }}:
                                        </span>
                                        <span class="font-semibold">{{ bill.quantity_of_bags_on_bl ||
                                            t('common.placeholders.notAvailable') }}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <span class="font-light">{{ t('bills.form.fields.wDunnageDribag.label') }}:
                                        </span>
                                        <span class="font-semibold">{{ bill.w_dunnage_dribag ?
                                            `${bill.w_dunnage_dribag}kg` : t('common.placeholders.notAvailable')
                                            }}</span>
                                    </div>
                                </div>

                                <!-- Row 5: B/L NET & NET -->
                                <div class="grid grid-cols-1 gap-x-6 gap-y-1 md:grid-cols-2 mb-1">
                                    <div class="flex gap-2">
                                        <span class="font-light">{{ t('bills.form.fields.netOnBl.label') }}: </span>
                                        <span class="font-semibold">{{ bill.net_on_bl ?
                                            `${bill.net_on_bl.toLocaleString()} kg` :
                                            t('common.placeholders.notAvailable') }}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <span class="font-light">{{ t('bills.show.labels.net') }}: </span>
                                        <span class="font-semibold">{{ formatOutturn(bill.average_outurn) }}</span>
                                    </div>
                                </div>

                                <!-- Inspection Period -->
                                <div class="grid grid-cols-1 gap-x-6 gap-y-1 md:grid-cols-2 mb-1">
                                    <div class="flex gap-2">
                                        <span class="font-light">{{ t('bills.show.labels.inspectionPeriod') }} </span>
                                        <span class="font-semibold">
                                            {{ bill.inspection_start_date ? new
                                                Date(bill.inspection_start_date).toLocaleDateString() :
                                                t('common.placeholders.notAvailable') }}
                                        </span>
                                        <span class="font-light">{{ t('bills.show.labels.to') }}</span>
                                        <span class="font-semibold">
                                            {{ bill.inspection_end_date ? new
                                                Date(bill.inspection_end_date).toLocaleDateString() :
                                                t('common.placeholders.notAvailable') }}
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <span class="font-light">{{ t('bills.form.fields.inspectionLocation.label') }}:
                                        </span>
                                        <span class="font-semibold">{{ bill.inspection_location ||
                                            t('common.placeholders.notAvailable') }}</span>
                                    </div>
                                </div>

                                <!-- Note -->
                                <div class="mt-3 border-t pt-2">
                                    <div class="flex gap-2">
                                        <span class="font-light">{{ t('bills.form.fields.note.label') }}:</span>
                                        <span class="text-sm">{{ bill.note }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Final Samples Section -->
                <Card class="gap-0">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <TestTube class="h-4 w-4" />
                                {{ t('bills.show.finalSamples.title') }}
                            </CardTitle>
                            <Button size="sm" variant="outline" @click="openCreateCuttingTestDialog">
                                <Plus class="h-3 w-3" />
                                {{ t('bills.show.finalSamples.add') }}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-0">
                        <div v-if="finalSamples.length > 0">
                            <CuttingTestTable :tests="finalSamples" :deleting-id="deletingCuttingTestId"
                                @edit="openEditCuttingTestDialog" @delete="openDeleteCuttingTestDialog" />
                        </div>
                        <div v-else class="py-6 text-center text-muted-foreground">
                            <TestTube class="mx-auto mb-2 h-8 w-8 opacity-50" />
                            <p class="text-sm font-medium">{{ t('bills.show.finalSamples.empty.title') }}</p>
                            <p class="text-xs">{{ t('bills.show.finalSamples.empty.subtitle') }}</p>
                            <Button size="sm" variant="outline" class="mt-3" @click="openCreateCuttingTestDialog">
                                <Plus class="mr-1 h-3 w-3" />
                                {{ t('bills.show.finalSamples.add') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Containers Section -->
            <Card class="gap-0">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <Package class="h-4 w-4" />
                            {{ t('bills.show.containers.title') }}
                        </CardTitle>
                        <Button size="sm" variant="outline" @click="openContainerDialog">
                            <Plus class="mr-1 h-3 w-3" />
                            {{ t('bills.show.containers.add') }}
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="pt-0">
                    <div v-if="containers.length > 0">
                        <ContainerTable :containers="containers" :deleting-id="deletingContainerId"
                            @edit="handleEditContainer" @delete="openDeleteContainerDialog" />
                    </div>
                    <div v-else class="py-6 text-center text-muted-foreground">
                        <Package class="mx-auto mb-2 h-8 w-8 opacity-50" />
                        <p class="text-sm font-medium">{{ t('bills.show.containers.empty.title') }}</p>
                        <p class="text-xs">{{ t('bills.show.containers.empty.subtitle') }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent class="max-h-[90vh] w-full max-w-4xl overflow-y-auto sm:max-w-4xl lg:max-w-5xl">
                <BillForm v-if="isEditDialogOpen" :bill="bill" :is-editing="true" @success="handleEditSuccess"
                    @cancel="handleEditCancel" />
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isContainerDialogOpen">
            <DialogContent class="max-h-[90vh] w-full max-w-4xl overflow-y-auto sm:max-w-4xl lg:max-w-5xl">
                <ContainerForm v-if="isContainerDialogOpen" :key="editingContainer
                    ? `edit-${editingContainer.id}`
                    : 'create'
                    " :bill="bill" :bill-id="bill.id" :container="editingContainer || undefined"
                    :is-editing="Boolean(editingContainer)" @success="handleContainerSuccess"
                    @cancel="handleContainerCancel" />
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ deleteDialogTitle }}</DialogTitle>
                    <DialogDescription>{{
                        deleteDialogDescription
                        }}</DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" :disabled="isDeleting" @click="closeDeleteDialog">
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button variant="destructive" :disabled="isDeleting" @click="confirmDelete">
                        <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
                        {{
                            isDeleting
                                ? t('common.states.deleting')
                                : t('common.actions.delete')
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isCuttingTestDialogOpen">
            <DialogContent class="max-h-[90vh] w-full max-w-5xl overflow-y-auto sm:max-w-5xl xl:max-w-6xl">
                <CuttingTestForm v-if="isCuttingTestDialogOpen" :bill-id="bill.id" :bill="bill"
                    :cutting-test="cuttingTestBeingEdited || undefined" :default-type="defaultFinalSampleType"
                    @success="handleCuttingTestSuccess" @cancel="handleCuttingTestCancel" />
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
