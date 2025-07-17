<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Management SPA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/bills.js'])
    <style>
        /* Simple transition for view switching */
        .view-hidden { display: none; }
        /* Prevent layout shift during loading */
        .min-h-table { min-height: 500px; }
    </style>
</head>
<body>

    <div 
        class="container mx-auto p-4" 
        x-data="billsApp()" 
        x-init="init()">

        <h1 class="text-3xl font-bold mb-4">Bill Management</h1>

        <template x-if="globalError">
            <div class="alert alert-error shadow-lg mb-4">
                <div>
                    <span><strong>Error:</strong> <span x-text="globalError"></span></span>
                </div>
                <div class="flex-none">
                    <button @click="globalError = ''" class="btn btn-sm btn-ghost">Dismiss</button>
                </div>
            </div>
        </template>
        
        <div x-show="loading" class="flex justify-center items-center p-8">
            <span class="loading loading-spinner loading-lg"></span>
        </div>

        <div :class="{ 'view-hidden': loading }">
            <div x-show="view === 'list'">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 p-4 bg-base-200 rounded-lg">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Search All</span></label>
                        <input type="text" placeholder="Search..." class="input input-bordered" x-model.debounce.500ms="queryParams.search">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Filter by Seller</span></label>
                        <input type="text" placeholder="Seller name..." class="input input-bordered" x-model.debounce.500ms="queryParams.filters.seller">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Sort By</span></label>
                        <select class="select select-bordered" x-model="queryParams.sort">
                            <option value="createdAt">Created (Newest)</option>
                            <option value="-createdAt">Created (Oldest)</option>
                            <option value="billNumber">Bill Number (Asc)</option>
                            <option value="-billNumber">Bill Number (Desc)</option>
                            <option value="seller">Seller (A-Z)</option>
                            <option value="-seller">Seller (Z-A)</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="btn btn-primary w-full" @click="fetchBills()">Apply</button>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-2xl">Bills</h2>
                    <button class="btn btn-primary" @click="changeView('create')">Create Bill</button>
                </div>

                <div class="overflow-x-auto min-h-table">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Bill Number</th>
                                <th>Seller</th>
                                <th>Buyer</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="bill in bills" :key="bill.id">
                                <tr>
                                    <td x-text="bill.billNumber"></td>
                                    <td x-text="bill.seller"></td>
                                    <td x-text="bill.buyer"></td>
                                    <td x-text="new Date(bill.createdAt).toLocaleDateString()"></td>
                                    <td class="flex gap-2">
                                        <button class="btn btn-sm btn-info" @click="changeView('detail', bill.id)">View</button>
                                        <button class="btn btn-sm btn-secondary" @click="changeView('edit', bill.id)">Edit</button>
                                        <button class="btn btn-sm btn-error" @click="confirmDelete(bill)">Delete</button>
                                    </td>
                                </tr>
                            </template>
                             <template x-if="bills.length === 0">
                                <tr><td colspan="5" class="text-center p-4">No bills found.</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-center" x-show="pagination.total > pagination.per_page">
                    <div class="btn-group">
                        <button class="btn" :disabled="!pagination.prev_page_url" @click="gotoPage(pagination.current_page - 1)">&laquo;</button>
                        <button class="btn" x-text="'Page ' + pagination.current_page"></button>
                        <button class="btn" :disabled="!pagination.next_page_url" @click="gotoPage(pagination.current_page + 1)">&raquo;</button>
                    </div>
                </div>
            </div>

            <div x-show="view === 'form'">
                 <h2 class="text-2xl mb-4" x-text="form.id ? 'Edit Bill' : 'Create Bill'"></h2>
                 <div class="bg-base-200 p-8 rounded-lg">
                    <form @submit.prevent="saveBill" class="space-y-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Bill Number</span></label>
                            <input type="text" placeholder="e.g., BN12345" class="input input-bordered" x-model="form.billNumber" :class="{'input-error': errors.billNumber}">
                            <label class="label" x-show="errors.billNumber"><span class="label-text-alt text-error" x-text="errors.billNumber"></span></label>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Seller</span></label>
                            <input type="text" placeholder="Seller Name" class="input input-bordered" x-model="form.seller" :class="{'input-error': errors.seller}">
                            <label class="label" x-show="errors.seller"><span class="label-text-alt text-error" x-text="errors.seller"></span></label>
                        </div>
                        
                        <div class="form-control">
                            <label class="label"><span class="label-text">Buyer</span></label>
                            <input type="text" placeholder="Buyer Name" class="input input-bordered" x-model="form.buyer" :class="{'input-error': errors.buyer}">
                            <label class="label" x-show="errors.buyer"><span class="label-text-alt text-error" x-text="errors.buyer"></span></label>
                        </div>

                        <div class="flex justify-end gap-4 mt-6">
                            <button type="button" class="btn" @click="changeView('list')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Bill</button>
                        </div>
                    </form>
                 </div>
            </div>

            <div x-show="view === 'detail' && currentBill">
                <button class="btn btn-link mb-4" @click="changeView('list')">&larr; Back to List</button>
                <div class="card bg-base-200 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title text-2xl" x-text="'Bill: ' + currentBill.billNumber"></h2>
                        <p><strong>ID:</strong> <span x-text="currentBill.id"></span></p>
                        <p><strong>Seller:</strong> <span x-text="currentBill.seller"></span></p>
                        <p><strong>Buyer:</strong> <span x-text="currentBill.buyer"></span></p>
                        <p><strong>Created:</strong> <span x-text="new Date(currentBill.createdAt).toLocaleString()"></span></p>
                        
                        <div x-show="currentBill.containers && currentBill.containers.length > 0" class="mt-4">
                            <h3 class="text-xl font-bold">Containers</h3>
                            <div class="divider mt-0"></div>
                            <ul class="list-disc pl-5 space-y-2">
                                <template x-for="container in currentBill.containers" :key="container.id">
                                    <li>
                                        <span x-text="`Container #${container.containerNumber} (Truck: ${container.truck})`"></span>
                                        <template x-if="container.cuttingTest">
                                            <div class="pl-4 text-sm opacity-80">
                                                Cutting Test: Outturn <span x-text="container.cuttingTest.outturnRate"></span>, Moisture <span x-text="container.cuttingTest.moisture"></span>
                                            </div>
                                        </template>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <dialog id="delete_modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Confirm Deletion</h3>
                <p class="py-4">Are you sure you want to delete Bill <strong x-text="billToDelete?.billNumber"></strong>? This action cannot be undone.</p>
                <div class="modal-action">
                    <form method="dialog" class="w-full flex justify-end gap-4">
                        <button class="btn" @click="billToDelete = null">Cancel</button>
                        <button class="btn btn-error" @click="deleteBill()">Delete</button>
                    </form>
                </div>
            </div>
        </dialog>
    </div>


</body>
</html>
