<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import ContainerForm from '@/components/containers/ContainerForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Container } from '@/types';
import * as containerRoutes from '@/routes/containers';
import { Head, router } from '@inertiajs/vue3';
import { Package, ArrowLeft } from 'lucide-vue-next';

interface Props {
    container: Container;
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

// Navigation
const goBack = () => {
    router.visit(containerRoutes.show.url(props.container.id.toString()));
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

    <Head :title="`Edit Container ${container.container_number || `#${container.id}`}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" @click="goBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Container
                    </Button>
                    <div class="flex items-center gap-2">
                        <Package class="h-6 w-6" />
                        <h1 class="text-2xl font-semibold">
                            Edit Container {{ container.container_number || `#${container.id}` }}
                        </h1>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <ContainerForm :container="container" :is-editing="true" @success="handleSuccess"
                        @cancel="handleCancel" />
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Bill Information -->
                    <Card v-if="container.bill">
                        <CardHeader>
                            <CardTitle>Bill Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Bill Number</Label>
                                <p class="font-medium">{{ container.bill.bill_number || `Bill #${container.bill.id}` }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Seller</Label>
                                <p>{{ container.bill.seller || '-' }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Buyer</Label>
                                <p>{{ container.bill.buyer || '-' }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Container Metadata -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Container Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                                <p class="text-sm">{{ new Date(container.created_at).toLocaleString() }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Last Updated</Label>
                                <p class="text-sm">{{ new Date(container.updated_at).toLocaleString() }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
