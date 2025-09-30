﻿<script setup lang="ts">
import ContainerForm from '@/components/containers/ContainerForm.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import * as containerRoutes from '@/routes/containers';
import { type Container } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Package } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    container: Container;
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();
const containerIdentifier = computed(
    () => props.container.container_number || `#${props.container.id}`,
);

// Navigation
const goBack = () => {
    const identifier =
        props.container.container_number || props.container.id.toString();
    router.visit(containerRoutes.show.url(identifier.toString()));
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
    <Head :title="$t('containers.edit.headTitle', { identifier: containerIdentifier })" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" @click="goBack">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        {{ $t('containers.edit.actions.back') }}
                    </Button>
                    <div class="flex items-center gap-2">
                        <Package class="h-6 w-6" />
                        <h1 class="text-2xl font-semibold">
                            {{
                                $t('containers.edit.title', {
                                    identifier: containerIdentifier,
                                })
                            }}
                        </h1>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <ContainerForm
                        :container="container"
                        :is-editing="true"
                        @success="handleSuccess"
                        @cancel="handleCancel"
                    />
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Bill Information -->
                    <Card v-if="container.bill">
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
                                        container.bill.bill_number ||
                                        $t('containers.edit.bill.fallback', {
                                            id: container.bill.id,
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
                                        container.bill.seller ||
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
                                        container.bill.buyer ||
                                        $t('common.placeholders.notAvailable')
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Container Metadata -->
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ $t('containers.edit.metadata.title') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">
                                    {{ $t('containers.edit.metadata.created') }}
                                </Label>
                                <p class="text-sm">
                                    {{
                                        new Date(
                                            container.created_at,
                                        ).toLocaleString()
                                    }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">
                                    {{ $t('containers.edit.metadata.updated') }}
                                </Label>
                                <p class="text-sm">
                                    {{
                                        new Date(
                                            container.updated_at,
                                        ).toLocaleString()
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
