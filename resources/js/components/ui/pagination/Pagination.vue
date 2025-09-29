<template>
    <nav
        role="navigation"
        aria-label="pagination"
        :class="cn('mx-auto flex w-full justify-center', $attrs.class as ClassValue | undefined)"
    >
        <div class="flex flex-row items-center gap-1">
            <slot />
        </div>
    </nav>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { ClassValue } from 'clsx'
</script>
