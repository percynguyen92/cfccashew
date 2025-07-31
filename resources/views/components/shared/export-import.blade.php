<!-- resources/views/components/shared/export-import.blade.php -->
<div x-data="exportImport()" class="flex flex-wrap gap-2">
    <!-- Export Dropdown -->
    <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Xuất dữ liệu
        </label>
        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
            <li>
                <a @click="exportData('excel')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z"></path>
                    </svg>
                    Excel (.xlsx)
                </a>
            </li>
            <li>
                <a @click="exportData('csv')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z"></path>
                    </svg>
                    CSV
                </a>
            </li>
            <li>
                <a @click="exportData('pdf')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z"></path>
                    </svg>
                    PDF
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Import Button -->
    <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
            Nhập dữ liệu
        </label>
        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
            <li>
                <a @click="openImportModal('excel')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z"></path>
                    </svg>
                    Excel (.xlsx)
                </a>
            </li>
            <li>
                <a @click="openImportModal('csv')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z"></path>
                    </svg>
                    CSV
                </a>
            </li>
            <li>
                <a href="/downloads/templates/bills-template.xlsx" download>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Tải template
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Import Modal -->
    <div x-show="showImportModal" class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Nhập dữ liệu từ file</h3>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Chọn file</span>
                </label>
                <input type="file" 
                       @change="handleFileSelect($event)"
                       :accept="importType === 'excel' ? '.xlsx,.xls' : '.csv'"
                       class="file-input file-input-bordered w-full">
                <label class="label">
                    <span class="label-text-alt">
                        Chỉ chấp nhận file <span x-text="importType.toUpperCase()"></span>
                    </span>
                </label>
            </div>
            
            <!-- Import Options -->
            <div class="form-control mt-4">
                <label class="label cursor-pointer">
                    <span class="label-text">Cập nhật nếu đã tồn tại</span>
                    <input type="checkbox" x-model="importOptions.updateExisting" class="checkbox">
                </label>
            </div>
            
            <div class="form-control">
                <label class="label cursor-pointer">
                    <span class="label-text">Bỏ qua dòng lỗi</span>
                    <input type="checkbox" x-model="importOptions.skipErrors" class="checkbox">
                </label>
            </div>
            
            <!-- Preview -->
            <div x-show="previewData.length > 0" class="mt-4">
                <h4 class="font-semibold mb-2">Xem trước dữ liệu:</h4>
                <div class="overflow-x-auto max-h-60">
                    <table class="table table-compact w-full">
                        <thead>
                            <tr>
                                <template x-for="header in previewHeaders" :key="header">
                                    <th x-text="header"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in previewData.slice(0, 5)" :key="index">
                                <tr>
                                    <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                        <td x-text="cell"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Hiển thị 5 dòng đầu tiên của <span x-text="previewData.length"></span> dòng dữ liệu
                </p>
            </div>
            
            <div class="modal-action">
                <button @click="closeImportModal()" class="btn btn-ghost">Hủy</button>
                <button @click="performImport()" 
                        :disabled="!selectedFile"
                        class="btn btn-primary">
                    <span x-show="!importing">Nhập dữ liệu</span>
                    <span x-show="importing" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Import Progress Modal -->
    <div x-show="showProgressModal" class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Đang nhập dữ liệu...</h3>
            
            <div class="space-y-4">
                <progress class="progress progress-primary w-full" 
                         :value="importProgress.current" 
                         :max="importProgress.total"></progress>
                         
                <div class="text-center">
                    <span x-text="`${importProgress.current}/${importProgress.total} (${Math.round(importProgress.current/importProgress.total*100)}%)`"></span>
                </div>
                
                <div x-show="importProgress.errors.length > 0" class="alert alert-warning">
                    <span>Có <span x-text="importProgress.errors.length"></span> lỗi trong quá trình nhập</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportImport() {
    return {
        showImportModal: false,
        showProgressModal: false,
        importType: 'excel',
        selectedFile: null,
        previewData: [],
        previewHeaders: [],
        importing: false,
        importOptions: {
            updateExisting: false,
            skipErrors: true
        },
        importProgress: {
            current: 0,
            total: 0,
            errors: []
        },
        
        async exportData(format) {
            try {
                const filters = this.$parent.filters || {};
                const params = new URLSearchParams({
                    format: format,
                    ...filters
                });
                
                const response = await fetch(`/api/v1/bills/export?${params}`);
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `bills_export_${new Date().toISOString().split('T')[0]}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    this.showAlert('Xuất dữ liệu thành công', 'success');
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                console.error('Export error:', error);
                this.showAlert('Có lỗi xảy ra khi xuất dữ liệu', 'error');
            }
        },
        
        openImportModal(type) {
            this.importType = type;
            this.showImportModal = true;
        },
        
        closeImportModal() {
            this.showImportModal = false;
            this.selectedFile = null;
            this.previewData = [];
            this.previewHeaders = [];
        },
        
        async handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            this.selectedFile = file;
            
            try {
                if (this.importType === 'csv') {
                    await this.previewCSV(file);
                } else {
                    await this.previewExcel(file);
                }
            } catch (error) {
                console.error('File preview error:', error);
                this.showAlert('Không thể đọc file. Vui lòng kiểm tra định dạng file.', 'error');
            }
        },
        
        async previewCSV(file) {
            const text = await file.text();
            const lines = text.split('\n').filter(line => line.trim());
            
            if (lines.length > 0) {
                this.previewHeaders = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
                this.previewData = lines.slice(1).map(line => 
                    line.split(',').map(cell => cell.trim().replace(/"/g, ''))
                );
            }
        },
        
        async previewExcel(file) {
            // This would require a library like SheetJS
            const formData = new FormData();
            formData.append('file', file);
            
            const response = await fetch('/api/v1/preview-excel', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const data = await response.json();
                this.previewHeaders = data.headers;
                this.previewData = data.rows;
            }
        },
        
        async performImport() {
            if (!this.selectedFile) return;
            
            this.importing = true;
            this.showImportModal = false;
            this.showProgressModal = true;
            
            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('options', JSON.stringify(this.importOptions));
            
            try {
                const response = await fetch('/api/v1/bills/import', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    
                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;
                        
                        const chunk = decoder.decode(value);
                        const lines = chunk.split('\n').filter(line => line.trim());
                        
                        lines.forEach(line => {
                            try {
                                const progress = JSON.parse(line);
                                this.importProgress = progress;
                            } catch (e) {
                                // Ignore invalid JSON lines
                            }
                        });
                    }
                    
                    this.showProgressModal = false;
                    this.showAlert('Nhập dữ liệu thành công', 'success');
                    this.$dispatch('bills-imported');
                } else {
                    throw new Error('Import failed');
                }
            } catch (error) {
                console.error('Import error:', error);
                this.showAlert('Có lỗi xảy ra khi nhập dữ liệu', 'error');
            } finally {
                this.importing = false;
                this.showProgressModal = false;
            }
        }
    }
}
</script>