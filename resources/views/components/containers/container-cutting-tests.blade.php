<!-- resources/views/components/containers/container-cutting-tests.blade.php -->
@props(['containerId'])

<div x-data="containerCuttingTests({{ $containerId }})" x-init="loadCuttingTests()">
    <div class="p-4">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-semibold text-lg">Kết quả cắt Container</h4>
            <button @click="openCreateModal()" class="btn btn-primary btn-sm">
                Thêm kết quả cắt
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="table table-compact w-full">
                <thead>
                    <tr>
                        <th>Loại</th>
                        <th>Độ ẩm (%)</th>
                        <th>Trọng lượng mẫu (g)</th>
                        <th>Số hạt</th>
                        <th>Nhân tốt (g)</th>
                        <th>Outturn Rate</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="test in cuttingTests" :key="test.id">
                        <tr>
                            <td>
                                <div class="badge badge-outline" x-text="getTestType(test.type)"></div>
                            </td>
                            <td x-text="test.moisture + '%'"></td>
                            <td x-text="test.sample_weight + 'g'"></td>
                            <td x-text="test.nut_count"></td>
                            <td x-text="test.w_good_kernel + 'g'"></td>
                            <td>
                                <div class="badge" 
                                     :class="test.outturn_rate > 25 ? 'badge-success' : 'badge-warning'"
                                     x-text="test.outturn_rate + ' lbs/80kg'">
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button @click="editTest(test)" class="btn btn-ghost btn-xs">
                                        Sửa
                                    </button>
                                    <button @click="deleteTest(test.id)" class="btn btn-ghost btn-xs text-error">
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            
            <!-- Empty State -->
            <div x-show="cuttingTests.length === 0" class="text-center py-8">
                <p class="text-gray-500">Chưa có kết quả cắt cho container này</p>
            </div>
        </div>
    </div>
    
    <!-- Container Cutting Test Modal -->
    <x-cutting-tests.cutting-test-modal-form type="container" :container-id="$containerId" />
</div>

<script>
function containerCuttingTests(containerId) {
    return {
        containerId: containerId,
        cuttingTests: [],
        
        async loadCuttingTests() {
            try {
                const response = await fetch(`/api/v1/containers/${this.containerId}/cutting-tests`);
                this.cuttingTests = await response.json();
            } catch (error) {
                console.error('Error loading cutting tests:', error);
            }
        },
        
        getTestType(type) {
            const types = {
                1: 'Final Sample 1st Cut',
                2: 'Final Sample 2nd Cut', 
                3: 'Final Sample 3rd Cut',
                4: 'Container Cut'
            };
            return types[type] || 'Unknown';
        },
        
        openCreateModal() {
            this.$dispatch('open-cutting-test-modal', {
                type: 'container',
                containerId: this.containerId
            });
        },
        
        editTest(test) {
            this.$dispatch('open-cutting-test-modal', {
                type: 'container',
                containerId: this.containerId,
                test: test
            });
        },
        
        async deleteTest(testId) {
            if (await this.confirmDelete('Bạn có chắc chắn muốn xóa kết quả cắt này?')) {
                try {
                    await fetch(`/api/v1/cutting-tests/${testId}`, { method: 'DELETE' });
                    await this.loadCuttingTests();
                    this.showAlert('Kết quả cắt đã được xóa', 'success');
                } catch (error) {
                    this.showAlert('Có lỗi xảy ra khi xóa', 'error');
                }
            }
        }
    }
}
</script>