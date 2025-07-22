<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bills Management System</title>

    <!-- Tailwind CSS + DaisyUI -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FontAwesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>

<body class="bg-base-300 font-sans antialiased">
    <div x-data="billsApp()" x-init="init()"
        class="min-h-screen flex flex-col bg-gradient-to-r from-cyan-500 to-blue-500">
        <!-- Main -->
        <main class="max-w-6xl mx-auto">
            <!-- Header -->
            <header class="shadow-lg rounded-b-lg sticky bg-base-200 top-0 z-50 mb-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center space-x-4">
                            <button x-show="view !== 'list'" @click="changeView('list')"
                                class="btn btn-ghost btn-circle">
                                <i class="fas fa-arrow-left text-lg"></i>
                            </button>
                            <h1 class="text-xl font-semibold" x-text="getPageTitle()"></h1>
                        </div>
                        <div x-show="view === 'list'" class="flex items-center space-x-3">
                            <button @click="changeView('create')" class="btn btn-primary flex items-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>New Bill</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Global error -->
            <template x-if="globalError">
                <div class="alert alert-error shadow-lg mb-6">
                    <div>
                        <i class="fas fa-exclamation-circle"></i>
                        <span x-text="globalError"></span>
                    </div>
                    <button @click="globalError=''" class="btn btn-sm btn-circle btn-ghost">
                        ✕
                    </button>
                </div>
            </template>

            <!-- Loading overlay -->
            <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
                style="display: none">
                <div class="loading-spinner loading-lg"></div>
            </div>

            <!-- List view -->
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

            <!-- Bill Form -->
            <section x-show="view === 'form'" x-transition>
                <div class="card bg-base-200 p-8 max-w-2xl mx-auto">
                    <form @submit.prevent="saveBill" class="space-y-6">
                        <div>
                            <label class="label"><span class="label-text">Số Bill</span></label>
                            <input type="text" x-model="form.billNumber"
                                :class="errors.billNumber ? 'input input-error' : 'input input-bordered'"
                                placeholder="Enter bill number" />
                            <p x-show="errors.billNumber" x-text="errors.billNumber" class="text-error mt-1"></p>
                        </div>
                        <div>
                            <label class="label"><span class="label-text">Người bán</span></label>
                            <input type="text" x-model="form.seller"
                                :class="errors.seller ? 'input input-error' : 'input input-bordered'"
                                placeholder="Enter seller name" />
                            <p x-show="errors.seller" x-text="errors.seller" class="text-error mt-1"></p>
                        </div>
                        <div>
                            <label class="label"><span class="label-text">Người mua</span></label>
                            <input type="text" x-model="form.buyer"
                                :class="errors.buyer ? 'input input-error' : 'input input-bordered'"
                                placeholder="Enter buyer name" />
                            <p x-show="errors.buyer" x-text="errors.buyer" class="text-error mt-1"></p>
                        </div>
                        <div class="flex justify-end space-x-4 pt-6 border-t border-base-content/20">
                            <button type="button" @click="changeView('list')" class="btn btn-outline">
                                Huỷ
                            </button>
                            <button type="submit" :disabled="loading"
                                :class="loading ? 'btn btn-primary loading' : 'btn btn-primary'">
                                Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Detail view -->
            <template x-if="view === 'detail' && currentBill">
                <section x-transition>
                    <div class="card bg-base-200 p-8 max-w-2xl mx-auto">
                        <h2 class="text-2xl font-bold mb-4" x-text="`Bill: ${currentBill.billNumber}`"></h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                            <div>
                                <dt class="font-semibold">Người bán</dt>
                                <dd x-text="currentBill.seller"></dd>
                            </div>
                            <div>
                                <dt class="font-semibold">Người mua</dt>
                                <dd x-text="currentBill.buyer"></dd>
                            </div>
                        </dl>
                        <div class="flex justify-between">
                            <button @click="changeView('list')" class="btn btn-outline">
                                Trở lại danh sách
                            </button>
                            <button @click="changeView('edit', currentBill.id)" class="btn btn-primary">
                                Sửa
                            </button>
                        </div>
                    </div>
                </section>
            </template>

            <!-- Delete Modal -->
            <div x-show="showDeleteModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                style="display: none">
                <div @click.away="showDeleteModal = false" class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="text-red-600 text-3xl">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="text-lg font-semibold">Xoá Bill</h3>
                    </div>
                    <p class="mb-6">
                        Bạn có chắc muốn xoá bill
                        <strong x-text="billToDelete?.billNumber"></strong>?
                    </p>
                    <div class="flex justify-end space-x-4">
                        <button @click="showDeleteModal = false" :disabled="loading" class="btn btn-outline">
                            Huỷ
                        </button>
                        <button @click="deleteBill" :disabled="loading" class="btn btn-error">
                            <template x-if="loading">
                                <span class="loading loading-spinner"></span>
                            </template>
                            Xoá
                        </button>
                    </div>
                </div>
            </div>
        </main>
        <x-dark-mode-toggle />
    </div>
</body>

</html>
