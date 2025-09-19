<template>
    <li :class="cn($attrs.class)">
        <slot />
    </li>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'
</script>