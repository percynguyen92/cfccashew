<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import * as bills from '@/routes/bills';
import type { Bill } from '@/types';
import { Head, router } from '@inertiajs/vue3';

interface Props {
    bill: Bill;
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

// Handle form success
const handleSuccess = () => {
    // The form component handles the redirect, so we don't need to do anything here
    // The success message will be shown via the flash message system
};

// Handle form cancel
const handleCancel = () => {
    router.visit(bills.show.url(props.bill.id));
};
</script>

<template>
    <Head title="Edit Bill" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Bill</h1>
            </div>
            <div class="flex justify-center">
                <BillForm
                    :bill="bill"
                    :is-editing="true"
                    @success="handleSuccess"
                    @cancel="handleCancel"
                />
            </div>
        </div>
    </AppLayout>
</template>
