<section v-show="view === 'form'" v-cloak>
    <div class="card bg-base-200 p-8 max-w-2xl mx-auto">
        <form @submit.prevent="saveBill" class="space-y-6">
            <div>
                <label class="label"><span class="label-text">Số Bill</span></label>
                <input type="text" v-model="form.billNumber"
                    :class="errors.billNumber ? 'input input-error' : 'input input-bordered'"
                    placeholder="Enter bill number" />
                <p v-show="errors.billNumber" v-text="errors.billNumber" class="text-error mt-1"></p>
            </div>
            <div>
                <label class="label"><span class="label-text">Người bán</span></label>
                <input type="text" v-model="form.seller"
                    :class="errors.seller ? 'input input-error' : 'input input-bordered'"
                    placeholder="Enter seller name" />
                <p v-show="errors.seller" v-text="errors.seller" class="text-error mt-1"></p>
            </div>
            <div>
                <label class="label"><span class="label-text">Người mua</span></label>
                <input type="text" v-model="form.buyer"
                    :class="errors.buyer ? 'input input-error' : 'input input-bordered'"
                    placeholder="Enter buyer name" />
                <p v-show="errors.buyer" v-text="errors.buyer" class="text-error mt-1"></p>
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
