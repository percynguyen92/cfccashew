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
