<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import {
  SelectIcon,
  SelectTrigger as SelectTriggerPrimitive,
  type SelectTriggerProps,
} from 'reka-ui'
import { ChevronDown } from 'lucide-vue-next'

const props = defineProps<SelectTriggerProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _class, ...delegated } = props

  return delegated
})
</script>

<template>
  <SelectTriggerPrimitive
    data-slot="select-trigger"
    v-bind="delegatedProps"
    :class="
      cn(
        'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1',
        props.class,
      )
    "
  >
    <slot />
    <SelectIcon>
      <ChevronDown class="size-4 opacity-50" />
    </SelectIcon>
  </SelectTriggerPrimitive>
</template>
