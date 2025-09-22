<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import {
  SelectContent as SelectContentPrimitive,
  type SelectContentEmits,
  type SelectContentProps,
  SelectPortal,
  SelectScrollDownButton,
  SelectScrollUpButton,
  SelectViewport,
  useForwardPropsEmits,
} from 'reka-ui'
import { ChevronDown, ChevronUp } from 'lucide-vue-next'

const props = withDefaults(
  defineProps<SelectContentProps & { class?: HTMLAttributes['class'] }>(),
  {
    position: 'popper',
    sideOffset: 4,
  },
)
const emits = defineEmits<SelectContentEmits>()

const delegatedProps = computed(() => {
  const { class: _class, ...delegated } = props

  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <SelectPortal>
    <SelectContentPrimitive
      data-slot="select-content"
      v-bind="forwarded"
      :class="
        cn(
          'relative z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
          props.class,
        )
      "
    >
      <SelectScrollUpButton
        class="flex h-8 cursor-default items-center justify-center bg-popover text-popover-foreground"
      >
        <ChevronUp class="size-4" />
      </SelectScrollUpButton>
      <SelectViewport class="p-1">
        <slot />
      </SelectViewport>
      <SelectScrollDownButton
        class="flex h-8 cursor-default items-center justify-center bg-popover text-popover-foreground"
      >
        <ChevronDown class="size-4" />
      </SelectScrollDownButton>
    </SelectContentPrimitive>
  </SelectPortal>
</template>
