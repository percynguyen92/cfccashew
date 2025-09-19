import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

export interface PaginationData {
  current_page: number
  data: any[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}

export function usePagination() {
  const isLoading = ref(false)

  const goToPage = (url: string | null) => {
    if (!url || isLoading.value) return

    isLoading.value = true
    router.visit(url, {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        isLoading.value = false
      },
    })
  }

  const getPaginationInfo = (data: PaginationData) => {
    return {
      from: data.from,
      to: data.to,
      total: data.total,
      currentPage: data.current_page,
      lastPage: data.last_page,
      hasNextPage: !!data.next_page_url,
      hasPrevPage: !!data.prev_page_url,
    }
  }

  return {
    isLoading,
    goToPage,
    getPaginationInfo,
  }
}