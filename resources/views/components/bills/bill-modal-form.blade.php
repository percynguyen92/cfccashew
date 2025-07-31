<!-- resources/views/components/bills/bill-modal-form.blade.php -->
<div x-data="{ 
    open: false, 
    form: { billNumber: '', seller: '', buyer: '' },
    isEdit: false 
}" 
     @open-bill-modal.window="
        open = true; 
        isEdit = !!$event.detail?.bill;
        form = $event.detail?.bill ? {...$event.detail.bill} : { billNumber: '', seller: '', buyer: '' };
     "
     @keydown.escape.window="open = false">
     
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal modal-open">
        
        <div class="modal-box w-11/12 max-w-2xl"
             @click.away="open = false">
             
            <h3 class="font-bold text-lg mb-4" 
                x-text="isEdit ? 'Sửa Bill' : 'Thêm Bill mới'"></h3>
            
            <form @submit.prevent="submitForm()">
                <div class="grid grid-cols-1 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Số Bill <span class="text-error">*</span></span>
                        </label>
                        <input type="text" 
                               x-model="form.billNumber" 
                               class="input input-bordered" 
                               placeholder="Nhập số Bill" 
                               required>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Người bán</span>
                        </label>
                        <input type="text" 
                               x-model="form.seller" 
                               class="input input-bordered" 
                               placeholder="Nhập tên người bán">
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Người mua</span>
                        </label>
                        <input type="text" 
                               x-model="form.buyer" 
                               class="input input-bordered" 
                               placeholder="Nhập tên người mua">
                    </div>
                </div>
                
                <div class="modal-action">
                    <button type="button" 
                            @click="open = false" 
                            class="btn btn-ghost">
                        Hủy
                    </button>
                    <button type="submit" 
                            class="btn btn-primary">
                        <span x-text="isEdit ? 'Cập nhật' : 'Tạo mới'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>