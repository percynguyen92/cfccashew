<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import * as bills from '@/routes/bills';
import { Head, router } from '@inertiajs/vue3';

const { breadcrumbs } = useBreadcrumbs();

// Handle form success
const handleSuccess = () => {
    // The form component handles the redirect, so we don't need to do anything here
    // The success message will be shown via the flash message system
};

// Handle form cancel
const handleCancel = () => {
    router.visit(bills.index.url());
};

// Expose callbacks for the future form implementation
defineExpose({
    handleSuccess,
    handleCancel,
});
</script>

<template>
    <Head title="Create Bill" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Create Bill</h1>
            </div>
            <div class="flex justify-center">
                <BillForm
                    :redirect-url="bills.index.url()"
                    @success="handleSuccess"
                    @cancel="handleCancel"
                />
            </div>
        </div>
    </AppLayout>
</template>
