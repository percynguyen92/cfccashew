<template>
    <tr :class="cn(
        'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted',
        $attrs.class as ClassValue | undefined
    )">
        <slot />
    </tr>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { ClassValue } from 'clsx'
</script>
