<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Head } from '@inertiajs/vue3';

interface Props {
    id: string;
}

defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();
</script>

<template>
    <Head :title="`Bill #${id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Bill #{{ id }}</h1>
            </div>
            <div class="relative min-h-[400px] flex-1 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <p class="text-muted-foreground">Bill details will be implemented here.</p>
            </div>
        </div>
    </AppLayout>
</template>