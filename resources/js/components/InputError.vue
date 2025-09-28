<script setup lang="ts">
import { computed } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    message?: string;
}>();

const isVisible = computed<boolean>(() => Boolean(props.message));
const displayMessage = computed(() => props.message || '\u00A0');
</script>

<template>
    <span
        v-bind="$attrs"
        :aria-hidden="!isVisible"
        aria-live="polite"
        class="min-h-[1.25rem] text-sm leading-5 flex items-center transition-opacity"
        :class="isVisible ? 'text-destructive opacity-100' : 'opacity-0'
        "
    >
        {{ displayMessage }}
    </span>
</template>
