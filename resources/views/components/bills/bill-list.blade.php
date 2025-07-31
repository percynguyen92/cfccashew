<!-- resources/views/components/bills/bill-list.blade.php -->
<div class="overflow-x-auto">
    <table class="table table-zebra w-full">
        <thead>
            <tr>
                <th>Số Bill</th>
                <th>Người bán</th>
                <th>Người mua</th>
                <th>Số Container</th>
                <th>Tổng trọng lượng</th>
                <th>Outturn TB</th>
                <th>Ngày tạo</th>
                <th class="text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="bill in bills" :key="bill.id">
                <x-bills.bill-item :bill="bill" />
            </template>
        </tbody>
    </table>
    
    <!-- Empty State -->
    <div x-show="bills.length === 0 && !loading" class="text-center py-12">
        <div class="text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Không có dữ liệu</h3>
            <p class="mt-1 text-sm text-gray-500">Bắt đầu bằng cách tạo bill mới.</p>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <x-shared.loading-spinner />
    </div>
</div>

<!-- Pagination -->
<div class="flex justify-between items-center mt-6">
    <div class="text-sm text-gray-700">
        Hiển thị <span x-text="pagination.from"></span> đến <span x-text="pagination.to"></span> 
        trong tổng số <span x-text="pagination.total"></span> kết quả
    </div>
    
    <div class="btn-group">
        <template x-for="page in pagination.links" :key="page.label">
            <button @click="page.url && loadBills(page.label)" 
                    :class="{ 'btn-active': page.active }"
                    :disabled="!page.url"
                    class="btn btn-sm"
                    x-html="page.label">
            </button>
        </template>
    </div>
</div>