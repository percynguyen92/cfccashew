<!-- resources/views/components/bills/bill-mobile-card.blade.php -->
<div class="card bg-base-100 shadow-md border">
    <div class="card-body p-4">
        <div class="flex justify-between items-start mb-2">
            <h3 class="font-bold text-lg" x-text="bill.billNumber"></h3>
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                    </svg>
                </label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-40">
                    <li><a @click="viewBillDetail(bill.id)">Chi tiết</a></li>
                    <li><a @click="editBill(bill)">Sửa</a></li>
                    <li><a @click="deleteBill(bill.id)" class="text-error">Xóa</a></li>
                </ul>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div>
                <span class="text-gray-500">Người bán:</span>
                <div class="font-medium" x-text="bill.seller || 'N/A'"></div>
            </div>
            <div>
                <span class="text-gray-500">Người mua:</span>
                <div class="font-medium" x-text="bill.buyer || 'N/A'"></div>
            </div>
            <div>
                <span class="text-gray-500">Containers:</span>
                <div class="badge badge-outline" x-text="bill.containers_count"></div>
            </div>
            <div>
                <span class="text-gray-500">Outturn TB:</span>
                <div class="badge" 
                     :class="bill.avg_outturn_rate > 25 ? 'badge-success' : 'badge-warning'"
                     x-text="bill.avg_outturn_rate ? bill.avg_outturn_rate + ' lbs/80kg' : 'N/A'">
                </div>
            </div>
        </div>
        
        <div class="text-xs text-gray-500 mt-2" 
             x-text="'Tạo: ' + new Date(bill.created_at).toLocaleDateString('vi-VN')">
        </div>
    </div>
</div>