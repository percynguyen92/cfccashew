<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { SelectValue as SelectValuePrimitive, type SelectValueProps } from 'reka-ui'

const props = defineProps<SelectValueProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _class, ...delegated } = props

  return delegated
})
</script>

<template>
  <SelectValuePrimitive
    data-slot="select-value"
    v-bind="delegatedProps"
    :class="cn('placeholder:text-muted-foreground', props.class)"
  >
    <slot />
  </SelectValuePrimitive>
</template>
