<!-- resources/views/components/bills/bill-item.blade.php -->
<tr>
    <td class="font-medium" x-text="bill.billNumber"></td>
    <td x-text="bill.seller"></td>
    <td x-text="bill.buyer"></td>
    <td>
        <div class="badge badge-outline" x-text="bill.containers_count"></div>
    </td>
    <td x-text="bill.total_weight + ' kg'"></td>
    <td>
        <div class="badge" 
             :class="bill.avg_outturn_rate > 45 ? 'badge-success' : 'badge-warning'"
             x-text="bill.avg_outturn_rate ? bill.avg_outturn_rate + ' lbs/80kg' : 'N/A'">
        </div>
    </td>
    <td x-text="new Date(bill.created_at).toLocaleDateString('vi-VN')"></td>
    <td class="text-center">
        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                </svg>
            </label>
            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                <li>
                    <a @click="viewBillDetail(bill.id)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Chi tiết
                    </a>
                </li>
                <li>
                    <a @click="editBill(bill)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Sửa
                    </a>
                </li>
                <li>
                    <a @click="deleteBill(bill.id)" class="text-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Xóa
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>