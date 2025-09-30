<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import ContainerForm from '@/components/containers/ContainerForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Bill } from '@/types';
import * as containerRoutes from '@/routes/containers';
import * as billRoutes from '@/routes/bills';
import { Head, router } from '@inertiajs/vue3';
import { Package, ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    bill?: Bill;
    bill_id?: number | string;
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();
const billIdentifier = computed(() =>
    props.bill
        ? props.bill.bill_number || `#${props.bill.id}`
        : null,
);

// Navigation
const goBack = () => {
    if (props.bill) {
        router.visit(billRoutes.show.url(props.bill.slug || props.bill.id.toString()));
    } else {
        router.visit(containerRoutes.index.url());
    }
};

// Form handlers
const handleSuccess = () => {
    // Will redirect to bill detail page as per controller
};

const handleCancel = () => {
    goBack();
};
</script>

<template>
    <Head :title="$t('containers.create.headTitle')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" @click="goBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        {{ $t('containers.create.actions.back') }}
                    </Button>
                    <div class="flex items-center gap-2">
                        <Package class="h-6 w-6" />
                        <h1 class="text-2xl font-semibold">
                            {{ $t('containers.create.title') }}
                        </h1>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <ContainerForm :bill="bill" :bill-id="bill_id" @success="handleSuccess" @cancel="handleCancel" />
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Bill Information -->
                    <Card v-if="bill">
                        <CardHeader>
                            <CardTitle>{{ $t('containers.edit.bill.title') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">
                                    {{ $t('containers.edit.bill.number') }}
                                </Label>
                                <p class="font-medium">
                                    {{
                                        billIdentifier ||
                                            $t('containers.edit.bill.fallback', {
                                                id: bill?.id,
                                            })
                                    }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">
                                    {{ $t('containers.edit.bill.seller') }}
                                </Label>
                                <p>
                                    {{
                                        bill.seller ||
                                        $t('common.placeholders.notAvailable')
                                    }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">
                                    {{ $t('containers.edit.bill.buyer') }}
                                </Label>
                                <p>
                                    {{
                                        bill.buyer ||
                                        $t('common.placeholders.notAvailable')
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
