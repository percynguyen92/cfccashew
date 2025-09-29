<template>
    <tbody :class="cn('[&_tr:last-child]:border-0', $attrs.class as ClassValue | undefined)">
        <slot />
    </tbody>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { ClassValue } from 'clsx'
</script>
