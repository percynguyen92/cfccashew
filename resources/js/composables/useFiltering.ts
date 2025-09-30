import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import { ref, watch } from 'vue';

export interface FilterOptions {
    search?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    [key: string]: any;
}

export function useFiltering(initialFilters: FilterOptions = {}) {
    const filters = ref<FilterOptions>({ ...initialFilters });
    const isLoading = ref(false);

    const normalizeSearchValue = (value: FilterOptions['search']) => {
        if (typeof value === 'string') {
            return value;
        }

        return value == null ? '' : String(value);
    };

    let lastSearchValue = normalizeSearchValue(initialFilters.search);

    const debouncedSearch = debounce((searchValue: string) => {
        updateFilters({ search: searchValue });
    }, 300);

    const updateFilters = (newFilters: Partial<FilterOptions>) => {
        if (isLoading.value) return;

        const updatedFilters = { ...filters.value, ...newFilters };

        // Remove empty values
        Object.keys(updatedFilters).forEach((key) => {
            if (
                updatedFilters[key] === '' ||
                updatedFilters[key] === null ||
                updatedFilters[key] === undefined
            ) {
                delete updatedFilters[key];
            }
        });

        filters.value = updatedFilters;

        isLoading.value = true;
        router.get(window.location.pathname, updatedFilters, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        });
    };

    const sortBy = (column: string) => {
        const currentSort = filters.value.sort_by;
        const currentDirection = filters.value.sort_direction || 'asc';

        let newDirection: 'asc' | 'desc' = 'asc';

        if (currentSort === column) {
            newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        }

        updateFilters({
            sort_by: column,
            sort_direction: newDirection,
        });
    };

    const clearFilters = () => {
        filters.value = {};
        updateFilters({});
    };

    // Watch for search changes
    watch(
        () => filters.value.search,
        (newSearch) => {
            const normalized = normalizeSearchValue(newSearch);

            if (normalized === lastSearchValue) {
                return;
            }

            lastSearchValue = normalized;
            debouncedSearch(normalized);
        },
    );

    return {
        filters,
        isLoading,
        updateFilters,
        sortBy,
        clearFilters,
        debouncedSearch,
    };
}
