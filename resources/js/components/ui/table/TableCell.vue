<template>
    <td :class="cn(
        'p-2 align-middle [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]',
        $attrs.class as ClassValue | undefined
    )">
        <slot />
    </td>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { ClassValue } from 'clsx'
</script>
