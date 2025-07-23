<section x-show="view === 'list'" x-transition>
    <!-- Search & Filters -->
    <div class="shadow-lg card bg-base-200 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <input type="text" placeholder="Tìm kiếm..." x-model.debounce.500ms="queryParams.search"
                class="input w-full col-span-2 focus:border-blue-500" />
            <div class="grid grid-cols-2 gap-4">
                <label for="sort" class="label"><span class="label-text">Sắp xếp theo</span></label>
                <select x-model="queryParams.sort" class="select select-bordered w-full">
                    <option value="createdAt">Mới nhất</option>
                    <option value="-createdAt">Cũ nhất</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Bills table desktop -->
    <div class="overflow-x-auto shadow-lg card bg-base-200">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Bill</th>
                    <th>Người bán</th>
                    <th>Người mua</th>
                    <th class="text-right">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="!loading && bills.length === 0">
                    <tr>
                        <td colspan="4" class="text-center py-12 text-gray-500">
                            <i class="fas fa-file-invoice text-4xl mb-2"></i>
                            <p>
                                Không tìm thấy Bill nào trong cơ
                                sở dữ liệu.
                            </p>
                            <p>
                                Tạo một bill mới để bắt đầu công
                                việc.
                            </p>
                        </td>
                    </tr>
                </template>
                <template x-for="bill in bills" :key="bill.id">
                    <tr class="hover:bg-base-300 transition-colors">
                        <td x-text="bill.billNumber"></td>
                        <td x-text="bill.seller"></td>
                        <td x-text="bill.buyer"></td>
                        <td class="text-right space-x-2">
                            <button @click="changeView('detail', bill.id)" class="btn btn-ghost btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button @click="changeView('edit', bill.id)"
                                class="btn btn-ghost btn-sm btn-success">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="confirmDelete(bill)" class="btn btn-ghost btn-sm btn-error">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</section>

<!-- Pagination -->
<nav class="bg-base-200 shadow-lg rounded-lg mt-4 p-4 flex justify-center items-center"
    x-show="pagination.last_page > 1">
    <button @click="gotoPage(Math.max(1, queryParams.page - 1))" :disabled="queryParams.page <= 1"
        class="btn btn-square btn-sm mx-1">
        <i class="fas fa-chevron-left"></i>
    </button>
    <template x-for="page in getPaginationPages()" :key="page">
        <button @click="gotoPage(page)"
            :class="page === queryParams.page ? 'btn btn-primary btn-sm mx-2' : 'btn btn-outline btn-sm mx-1'"
            x-text="page"></button>
    </template>
    <button @click="gotoPage(Math.min(pagination.last_page, queryParams.page + 1))"
        :disabled="queryParams.page >= pagination.last_page" class="btn btn-square btn-sm mx-1">
        <i class="fas fa-chevron-right"></i>
    </button>
</nav>
