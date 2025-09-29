<template>
    <div class="relative w-full overflow-auto">
        <table :class="cn('w-full caption-bottom text-sm', $attrs.class as ClassValue | undefined)">
            <slot />
        </table>
    </div>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { ClassValue } from 'clsx'
</script>
