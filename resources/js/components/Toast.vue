<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface ToastMessage {
    id: string;
    type: 'success' | 'error';
    message: string;
}

const page = usePage();
const toasts = ref<ToastMessage[]>([]);

// Watch for flash messages from the server
watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) {
            addToast('success', flash.success);
        }
        if (flash?.error) {
            addToast('error', flash.error);
        }
    },
    { immediate: true, deep: true },
);

const addToast = (type: 'success' | 'error', message: string) => {
    const id = Date.now().toString();
    toasts.value.push({ id, type, message });

    // Auto-remove after 5 seconds
    setTimeout(() => {
        removeToast(id);
    }, 5000);
};

const removeToast = (id: string) => {
    const index = toasts.value.findIndex((toast) => toast.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

// Expose addToast for programmatic use
defineExpose({ addToast });
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <div
            v-for="toast in toasts"
            :key="toast.id"
            :class="[
                'flex max-w-sm items-center gap-3 rounded-lg border p-4 shadow-lg',
                'transform transition-all duration-300 ease-in-out',
                toast.type === 'success'
                    ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200'
                    : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200',
            ]"
        >
            <CheckCircle
                v-if="toast.type === 'success'"
                class="h-5 w-5 flex-shrink-0"
            />
            <AlertCircle v-else class="h-5 w-5 flex-shrink-0" />

            <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>

            <Button
                variant="ghost"
                size="sm"
                @click="removeToast(toast.id)"
                class="h-6 w-6 p-0 hover:bg-transparent"
            >
                <X class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
