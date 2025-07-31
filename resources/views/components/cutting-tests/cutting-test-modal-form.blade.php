<!-- resources/views/components/cutting-tests/cutting-test-modal-form.blade.php -->
@props(['type' => 'bill', 'containerId' => null])

<div x-data="cuttingTestModal('{{ $type }}', {{ $containerId }})"
     @open-cutting-test-modal.window="handleModalOpen($event.detail)">
     
    <div x-show="open" class="modal modal-open">
        <div class="modal-box w-11/12 max-w-4xl">
            <h3 class="font-bold text-lg mb-4" x-text="modalTitle"></h3>
            
            <form @submit.prevent="submitForm()">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    
                    <!-- Type Selection (for container tests) -->
                    <div x-show="type === 'container'" class="form-control">
                        <label class="label">
                            <span class="label-text">Loại cắt <span class="text-error">*</span></span>
                        </label>
                        <select x-model="form.type" class="select select-bordered" required>
                            <option value="">Chọn loại cắt</option>
                            <option value="1">Final Sample 1st Cut</option>
                            <option value="2">Final Sample 2nd Cut</option>
                            <option value="3">Final Sample 3rd Cut</option>
                            <option value="4">Container Cut</option>
                        </select>
                    </div>
                    
                    <!-- Moisture -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Độ ẩm (%)</span>
                        </label>
                        <input type="number" 
                               x-model="form.moisture" 
                               step="0.01" 
                               min="0" 
                               max="100"
                               class="input input-bordered" 
                               placeholder="0.00">
                    </div>
                    
                    <!-- Sample Weight -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">TL mẫu (gram) <span class="text-error">*</span></span>
                        </label>
                        <input type="number" 
                               x-model="form.sample_weight" 
                               min="1"
                               class="input input-bordered" 
                               placeholder="1000"
                               required>
                    </div>
                    
                    <!-- Nut Count -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Số hạt</span>
                        </label>
                        <input type="number" 
                               x-model="form.nut_count" 
                               min="0"
                               class="input input-bordered" 
                               placeholder="0">
                    </div>
                    
                    <!-- Reject Nut Weight -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">TL hạt lỗi hoàn toàn (g)</span>
                        </label>
                        <input type="number" 
                               x-model="form.w_reject_nut" 
                               min="0"
                               class="input input-bordered" 
                               placeholder="0">
                    </div>
                    
                    <!-- Defective Nut Weight -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">TL hạt lỗi một phần (g)</span>
                        </label>
                        <input type="number" 
                               x-model="form.w_defective_nut" 
                               min="0"
                               class="input input-bordered" 
                               placeholder="0">
                    </div>
                    
                    <!-- Defective Kernel Weight -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">TL nhân lỗi một phần (g)</span>
                        </label>
                        <input type="number" 
                               x-model="form.w_defective_kernel" 
                               min="0"
                               class="input input-bordered" 
                               placeholder="0">
                    </div>
                    
                    <!-- Good Kernel Weight -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">TL nhân tốt (g) <span class="text-error">*</span></span>
                        </label>
                        <input type="number" 
                               x-model="form.w_good_kernel" 
                               min="0"
                               @input="calculateOutturnRate()"
                               class="input input-bordered" 
                               placeholder="0"
                               required>
                    </div>
                    
                    <!-- Sample Weight After Cut -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">TL mẫu sau cắt (g)</span>
                        </label>
                        <input type="number" 
                               x-model="form.w_sample_after_cut" 
                               min="0"
                               class="input input-bordered" 
                               placeholder="0">
                    </div>
                    
                    <!-- Calculated Outturn Rate -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Thu hồi nhân (lbs/80kg)</span>
                        </label>
                        <input type="number" 
                               x-model="form.outturn_rate" 
                               step="0.01"
                               class="input input-bordered bg-base-200" 
                               readonly>
                    </div>
                </div>
                
                <div class="modal-action">
                    <button type="button" @click="closeModal()" class="btn btn-ghost">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span x-text="isEdit ? 'Cập nhật' : 'Tạo mới'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cuttingTestModal(modalType, containerId) {
    return {
        open: false,
        type: modalType,
        containerId: containerId,
        isEdit: false,
        modalTitle: '',
        form: {
            type: modalType === 'container' ? '' : '1',
            moisture: '',
            sample_weight: 1000,
            nut_count: '',
            w_reject_nut: '',
            w_defective_nut: '',
            w_defective_kernel: '',
            w_good_kernel: '',
            w_sample_after_cut: '',
            outturn_rate: ''
        },
        
        handleModalOpen(detail) {
            if (detail.type !== this.type) return;
            if (this.type === 'container' && detail.containerId !== this.containerId) return;
            
            this.open = true;
            this.isEdit = !!detail.test;
            this.modalTitle = this.isEdit ? 'Sửa kết quả cắt' : 'Thêm kết quả cắt mới';
            
            if (detail.test) {
                this.form = { ...detail.test };
            } else {
                this.resetForm();
            }
        },
        
        closeModal() {
            this.open = false;
            this.resetForm();
        },
        
        resetForm() {
            this.form = {
                type: this.type === 'container' ? '' : '1',
                moisture: '',
                sample_weight: 1000,
                nut_count: '',
                w_reject_nut: '',
                w_defective_nut: '',
                w_defective_kernel: '',
                w_good_kernel: '',
                w_sample_after_cut: '',
                outturn_rate: ''
            };
        },
        
        calculateOutturnRate() {
            const goodKernel = parseFloat(this.form.w_good_kernel) || 0;
            const sampleWeight = parseFloat(this.form.sample_weight) || 1000;
            
            if (goodKernel > 0 && sampleWeight > 0) {
                // Formula: (Good Kernel Weight / Sample Weight) * 80 * 2.2046 (kg to lbs)
                const outturnRate = (goodKernel / sampleWeight) * 80 * 2.2046;
                this.form.outturn_rate = outturnRate.toFixed(2);
            } else {
                this.form.outturn_rate = '';
            }
        },
        
        async submitForm() {
            try {
                const url = this.isEdit 
                    ? `/api/v1/cutting-tests/${this.form.id}`
                    : (this.type === 'container' 
                        ? `/api/v1/containers/${this.containerId}/cutting-tests`
                        : `/api/v1/bills/${this.$parent.billId}/cutting-tests`);
                        
                const method = this.isEdit ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });
                
                if (response.ok) {
                    this.closeModal();
                    // Reload data based on type
                    if (this.type === 'container') {
                        this.$parent.loadCuttingTests();
                    } else {
                        this.$parent.loadBillCuttingTests();
                    }
                    this.showAlert('Kết quả cắt đã được lưu thành công', 'success');
                } else {
                    throw new Error('Failed to save cutting test');
                }
            } catch (error) {
                console.error('Error saving cutting test:', error);
                this.showAlert('Có lỗi xảy ra khi lưu kết quả cắt', 'error');
            }
        }
    }
}
</script>