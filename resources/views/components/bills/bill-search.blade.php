<!-- resources/views/components/bills/bill-search.blade.php -->
<div x-data="billSearch()" class="space-y-4">
    <!-- Basic Search -->
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" 
                   x-model="filters.search" 
                   @input="debounceSearch()"
                   class="input input-bordered w-full" 
                   placeholder="Tìm kiếm theo số Bill, người bán, người mua...">
        </div>
        <div class="flex gap-2">
            <button @click="search()" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Tìm kiếm
            </button>
            <button @click="toggleAdvanced()" class="btn btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
                Bộ lọc
            </button>
            <button @click="clearFilters()" class="btn btn-ghost">
                Xóa bộ lọc
            </button>
        </div>
    </div>
    
    <!-- Advanced Filters -->
    <div x-show="showAdvanced" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="card bg-base-200 p-4"
         style="display: none;">
         
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Từ ngày</span>
                </label>
                <input type="date" 
                       x-model="filters.date_from" 
                       class="input input-bordered input-sm">
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Đến ngày</span>
                </label>
                <input type="date" 
                       x-model="filters.date_to" 
                       class="input input-bordered input-sm">
            </div>
            
            <!-- Container Count Range -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Số container tối thiểu</span>
                </label>
                <input type="number" 
                       x-model="filters.min_containers" 
                       min="0"
                       class="input input-bordered input-sm" 
                       placeholder="0">
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Số container tối đa</span>
                </label>
                <input type="number" 
                       x-model="filters.max_containers" 
                       min="0"
                       class="input input-bordered input-sm" 
                       placeholder="100">
            </div>
            
            <!-- Outturn Rate Range -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Outturn tối thiểu (lbs/80kg)</span>
                </label>
                <input type="number" 
                       x-model="filters.min_outturn" 
                       step="0.1"
                       min="0"
                       class="input input-bordered input-sm" 
                       placeholder="0">
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Outturn tối đa (lbs/80kg)</span>
                </label>
                <input type="number" 
                       x-model="filters.max_outturn" 
                       step="0.1"
                       min="0"
                       class="input input-bordered input-sm" 
                       placeholder="50">
            </div>
            
            <!-- Sorting -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Sắp xếp theo</span>
                </label>
                <select x-model="filters.sort_by" class="select select-bordered select-sm">
                    <option value="created_at">Ngày tạo</option>
                    <option value="billNumber">Số Bill</option>
                    <option value="seller">Người bán</option>
                    <option value="buyer">Người mua</option>
                    <option value="containers_count">Số container</option>
                    <option value="avg_outturn_rate">Outturn trung bình</option>
                </select>
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Thứ tự</span>
                </label>
                <select x-model="filters.sort_direction" class="select select-bordered select-sm">
                    <option value="desc">Giảm dần</option>
                    <option value="asc">Tăng dần</option>
                </select>
            </div>
        </div>
        
        <div class="flex justify-end mt-4 gap-2">
            <button @click="applyFilters()" class="btn btn-primary btn-sm">
                Áp dụng bộ lọc
            </button>
            <button @click="resetAdvancedFilters()" class="btn btn-outline btn-sm">
                Đặt lại
            </button>
        </div>
    </div>
    
    <!-- Active filters display -->
    <div x-show="hasActiveFilters()" class="flex flex-wrap gap-2">
        <span class="text-sm text-gray-600">Bộ lọc đang áp dụng:</span>
        
        <template x-for="filter in getActiveFilters()" :key="filter.key">
            <div class="badge badge-primary gap-2">
                <span x-text="filter.label + ': ' + filter.value"></span>
                <button @click="removeFilter(filter.key)" class="btn btn-ghost btn-xs">×</button>
            </div>
        </template>
    </div>
</div>

<script>
function billSearch() {
    return {
        showAdvanced: false,
        searchTimeout: null,
        filters: {
            search: '',
            date_from: '',
            date_to: '',
            min_containers: '',
            max_containers: '',
            min_outturn: '',
            max_outturn: '',
            sort_by: 'created_at',
            sort_direction: 'desc'
        },
        
        toggleAdvanced() {
            this.showAdvanced = !this.showAdvanced;
        },
        
        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.search();
            }, 500);
        },
        
        search() {
            this.$dispatch('bills-search', this.filters);
        },
        
        applyFilters() {
            this.search();
            this.showAdvanced = false;
        },
        
        clearFilters() {
            this.filters = {
                search: '',
                date_from: '',
                date_to: '',
                min_containers: '',
                max_containers: '',
                min_outturn: '',
                max_outturn: '',
                sort_by: 'created_at',
                sort_direction: 'desc'
            };
            this.search();
        },
        
        resetAdvancedFilters() {
            this.filters.date_from = '';
            this.filters.date_to = '';
            this.filters.min_containers = '';
            this.filters.max_containers = '';
            this.filters.min_outturn = '';
            this.filters.max_outturn = '';
            this.filters.sort_by = 'created_at';
            this.filters.sort_direction = 'desc';
        },
        
        hasActiveFilters() {
            return Object.values(this.filters).some(value => 
                value !== '' && value !== 'created_at' && value !== 'desc'
            );
        },
        
        getActiveFilters() {
            const active = [];
            const labels = {
                search: 'Tìm kiếm',
                date_from: 'Từ ngày',
                date_to: 'Đến ngày',
                min_containers: 'Container tối thiểu',
                max_containers: 'Container tối đa',
                min_outturn: 'Outturn tối thiểu',
                max_outturn: 'Outturn tối đa',
                sort_by: 'Sắp xếp theo',
                sort_direction: 'Thứ tự'
            };
            
            for (const [key, value] of Object.entries(this.filters)) {
                if (value && value !== '' && 
                    !(key === 'sort_by' && value === 'created_at') &&
                    !(key === 'sort_direction' && value === 'desc')) {
                    active.push({
                        key: key,
                        label: labels[key],
                        value: value
                    });
                }
            }
            
            return active;
        },
        
        removeFilter(key) {
            this.filters[key] = '';
            if (key === 'sort_by') this.filters[key] = 'created_at';
            if (key === 'sort_direction') this.filters[key] = 'desc';
            this.search();
        }
    }
}
</script>