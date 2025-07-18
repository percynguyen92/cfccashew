<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bills SPA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-4 font-sans">

    <div
        x-data="billsApp()"
        x-init="init()"
        class="max-w-5xl mx-auto bg-white p-6 rounded shadow"
    >

        <!-- Global error -->
        <template x-if="globalError">
            <div class="bg-red-100 text-red-800 p-2 mb-4 rounded" x-text="globalError"></div>
        </template>

        <!-- Views -->
        <template x-if="view === 'list'">
            <div>
                <h1 class="text-xl font-bold mb-4">Bills List</h1>

                <!-- Search -->
                <input
                    type="text"
                    placeholder="Search bills..."
                    x-model="queryParams.search"
                    class="border p-2 rounded mb-4 w-full"
                />

                <!-- Table -->
                <table class="w-full table-auto border-collapse border border-gray-300">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 p-2">Bill #</th>
                            <th class="border border-gray-300 p-2">Seller</th>
                            <th class="border border-gray-300 p-2">Buyer</th>
                            <th class="border border-gray-300 p-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="loading">
                            <tr><td colspan="4" class="p-4 text-center">Loading...</td></tr>
                        </template>
                        <template x-for="bill in bills" :key="bill.id">
                            <tr>
                                <td class="border border-gray-300 p-2" x-text="bill.billNumber"></td>
                                <td class="border border-gray-300 p-2" x-text="bill.seller"></td>
                                <td class="border border-gray-300 p-2" x-text="bill.buyer"></td>
                                <td class="border border-gray-300 p-2 space-x-2">
                                    <button
                                        class="bg-blue-500 text-white px-2 py-1 rounded"
                                        @click="changeView('detail', bill.id)"
                                    >View</button>
                                    <button
                                        class="bg-green-500 text-white px-2 py-1 rounded"
                                        @click="changeView('edit', bill.id)"
                                    >Edit</button>
                                    <button
                                        class="bg-red-600 text-white px-2 py-1 rounded"
                                        @click="confirmDelete(bill)"
                                    >Delete</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-4 flex space-x-2">
                    <template x-for="page in pagination.last_page" :key="page">
                        <button
                            class="px-3 py-1 border rounded"
                            :class="{'bg-blue-500 text-white': page === queryParams.page}"
                            @click="gotoPage(page)"
                            x-text="page"
                        ></button>
                    </template>
                </div>

                <!-- Create new -->
                <button
                    class="mt-6 bg-indigo-600 text-white px-4 py-2 rounded"
                    @click="changeView('create')"
                >New Bill</button>
            </div>
        </template>

        <!-- Form View -->
        <template x-if="view === 'form'">
            <div>
                <h1 class="text-xl font-bold mb-4" x-text="form.id ? 'Edit Bill' : 'Create Bill'"></h1>

                <form @submit.prevent="saveBill" class="space-y-4 max-w-md">
                    <div>
                        <label class="block font-semibold mb-1">Bill Number</label>
                        <input
                            type="text"
                            x-model="form.billNumber"
                            class="border p-2 rounded w-full"
                        />
                        <div class="text-red-600 text-sm" x-text="errors.billNumber"></div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Seller</label>
                        <input
                            type="text"
                            x-model="form.seller"
                            class="border p-2 rounded w-full"
                        />
                        <div class="text-red-600 text-sm" x-text="errors.seller"></div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Buyer</label>
                        <input
                            type="text"
                            x-model="form.buyer"
                            class="border p-2 rounded w-full"
                        />
                        <div class="text-red-600 text-sm" x-text="errors.buyer"></div>
                    </div>

                    <div class="space-x-2">
                        <button
                            type="submit"
                            class="bg-green-600 text-white px-4 py-2 rounded"
                            :disabled="loading"
                        >Save</button>
                        <button
                            type="button"
                            class="bg-gray-400 text-black px-4 py-2 rounded"
                            @click="changeView('list')"
                        >Cancel</button>
                    </div>
                </form>
            </div>
        </template>

        <!-- Detail View -->
        <template x-if="view === 'detail' && currentBill">
            <div>
                <h1 class="text-xl font-bold mb-4" x-text="'Bill Detail: ' + currentBill.billNumber"></h1>

                <div>
                    <p><strong>Seller:</strong> <span x-text="currentBill.seller"></span></p>
                    <p><strong>Buyer:</strong> <span x-text="currentBill.buyer"></span></p>
                </div>

                <button
                    class="mt-6 bg-gray-400 px-4 py-2 rounded"
                    @click="changeView('list')"
                >Back to list</button>
            </div>
        </template>

        <!-- Delete Modal -->
        <dialog id="delete_modal" class="rounded p-6 shadow-lg border border-gray-300">
            <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
            <p>Are you sure you want to delete bill <strong x-text="billToDelete ? billToDelete.billNumber : ''"></strong>?</p>
            <div class="mt-6 space-x-2">
                <button
                    class="bg-red-600 text-white px-4 py-2 rounded"
                    @click="deleteBill"
                    :disabled="loading"
                >Delete</button>
                <button
                    class="bg-gray-400 px-4 py-2 rounded"
                    @click="document.getElementById('delete_modal').close()"
                    :disabled="loading"
                >Cancel</button>
            </div>
        </dialog>
    </div>
</body>
</html>
