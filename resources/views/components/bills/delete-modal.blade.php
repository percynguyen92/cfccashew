<div v-show="showDeleteModal"
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
            <strong v-text="billToDelete?.billNumber"></strong>?
        </p>
        <div class="flex justify-end space-x-4">
            <button @click="showDeleteModal = false" :disabled="loading" class="btn btn-outline">
                Huỷ
            </button>
            <button @click="deleteBill" :disabled="loading" class="btn btn-error">
                <template v-if="loading">
                    <span class="loading loading-spinner"></span>
                </template>
                Xoá
            </button>
        </div>
    </div>
</div>
