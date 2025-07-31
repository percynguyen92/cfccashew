<!-- resources/views/components/shared/confirm-modal.blade.php -->
<div x-data="{ 
    open: false, 
    title: '', 
    message: '', 
    confirmCallback: null 
}" 
     @open-confirm-modal.window="
        open = true;
        title = $event.detail.title || 'Xác nhận';
        message = $event.detail.message || 'Bạn có chắc chắn muốn thực hiện hành động này?';
        confirmCallback = $event.detail.callback;
     ">
     
    <div x-show="open" class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg" x-text="title"></h3>
            <p class="py-4" x-text="message"></p>
            <div class="modal-action">
                <button @click="open = false" class="btn btn-ghost">Hủy</button>
                <button @click="confirmCallback && confirmCallback(); open = false" 
                        class="btn btn-error">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>