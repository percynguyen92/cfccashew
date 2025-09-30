<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

type AppearanceMode = 'light' | 'dark' | 'system';

const { appearance, updateAppearance } = useAppearance();
const systemPrefersDark = ref(false);
let mediaQuery: MediaQueryList | null = null;

const handleSystemChange = (event: MediaQueryListEvent) => {
    systemPrefersDark.value = event.matches;
};

onMounted(() => {
    if (typeof window === 'undefined') {
        return;
    }

    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    systemPrefersDark.value = mediaQuery.matches;
    mediaQuery.addEventListener('change', handleSystemChange);
});

onBeforeUnmount(() => {
    mediaQuery?.removeEventListener('change', handleSystemChange);
});

const isDark = computed(() => {
    const current = appearance.value as AppearanceMode;

    if (current === 'system') {
        return systemPrefersDark.value;
    }

    return current === 'dark';
});

function toggleTheme() {
    updateAppearance(isDark.value ? 'light' : 'dark');
}
</script>

<template>
    <Button
        type="button"
        variant="ghost"
        size="icon"
        class="shrink-0 rounded-lg border border-sidebar-border/70 bg-sidebar/80 text-sidebar-foreground shadow-none transition hover:bg-accent/70 hover:text-accent-foreground group-data-[collapsible=icon]:hidden"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        @click="toggleTheme"
    >
        <Sun v-if="!isDark" class="h-5 w-5" />
        <Moon v-else class="h-5 w-5" />
    </Button>
</template>

