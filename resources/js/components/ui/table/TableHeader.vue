<template>
    <thead :class="cn('[&_tr]:border-b', $attrs.class as ClassValue | undefined)">
        <slot />
    </thead>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { ClassValue } from 'clsx'
</script>
