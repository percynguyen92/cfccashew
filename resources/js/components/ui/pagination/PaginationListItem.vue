<template>
    <li :class="cn($attrs.class as ClassValue | undefined)">
        <slot />
    </li>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
import type { ClassValue } from 'clsx'
</script>
