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
