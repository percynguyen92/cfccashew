<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import {
  SelectItem as SelectItemPrimitive,
  type SelectItemProps,
  SelectItemIndicator,
  SelectItemText,
} from 'reka-ui'
import { Check } from 'lucide-vue-next'

const props = defineProps<SelectItemProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _class, ...delegated } = props

  return delegated
})
</script>

<template>
  <SelectItemPrimitive
    data-slot="select-item"
    v-bind="delegatedProps"
    :class="
      cn(
        'relative flex w-full cursor-default select-none items-center gap-2 rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
        props.class,
      )
    "
  >
    <SelectItemIndicator class="absolute right-2 flex size-4 items-center justify-center">
      <Check class="size-4" />
    </SelectItemIndicator>
    <SelectItemText>
      <slot />
    </SelectItemText>
  </SelectItemPrimitive>
</template>
