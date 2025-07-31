<!-- resources/views/pages/bills/show.blade.php -->
<x-layout.app title="Chi tiết Bill" :breadcrumbs="[
    ['name' => 'Danh sách Bills', 'url' => route('bills.index')],
    ['name' => 'Chi tiết Bill: ' . $bill->billNumber]
]">
    <div x-data="billDetailManager({{ $bill->id }})" x-init="init()">
        
        <!-- Back Button & Title -->
        <div class="flex items-center mb-6">
            <a href="{{ route('bills.index') }}" class="btn btn-ghost btn-sm mr-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Quay lại
            </a>
            <h1 class="text-3xl font-bold">Chi tiết Bill: {{ $bill->billNumber }}</h1>
        </div>
        
        <!-- Bill Information -->
        <x-bills.bill-detail :bill="$bill" />
        
        <!-- Bill Cutting Tests Section -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="card-title">Kết quả cắt mẫu Bill (Final Sample)</h2>
                    <button @click="openCreateBillCuttingTestModal()" class="btn btn-primary btn-sm">
                        Thêm kết quả cắt
                    </button>
                </div>
                <x-cutting-tests.cutting-test-list type="bill" />
            </div>
        </div>
        
        <!-- Containers Section -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="card-title">Danh sách Containers</h2>
                    <button @click="openCreateContainerModal()" class="btn btn-primary btn-sm">
                        Thêm Container
                    </button>
                </div>
                <x-containers.container-list />
            </div>
        </div>
        
        <!-- Modals -->
        <x-containers.container-modal-form />
        <x-cutting-tests.cutting-test-modal-form />
        <x-shared.confirm-modal />
    </div>

    @push('scripts')
    <script>
        function billDetailManager(billId) {
            return {
                billId: billId,
                bill: {},
                containers: [],
                billCuttingTests: [],
                expandedContainers: [],
                
                async init() {
                    await this.loadBillDetail();
                    await this.loadContainers();
                    await this.loadBillCuttingTests();
                },
                
                async loadBillDetail() {
                    const response = await fetch(`/api/v1/bills/${this.billId}`);
                    this.bill = await response.json();
                },
                
                async loadContainers() {
                    const response = await fetch(`/api/v1/bills/${this.billId}/containers`);
                    this.containers = await response.json();
                },
                
                async loadBillCuttingTests() {
                    const response = await fetch(`/api/v1/bills/${this.billId}/cutting-tests`);
                    this.billCuttingTests = await response.json();
                },
                
                toggleContainerTests(containerId) {
                    const index = this.expandedContainers.indexOf(containerId);
                    if (index > -1) {
                        this.expandedContainers.splice(index, 1);
                    } else {
                        this.expandedContainers.push(containerId);
                    }
                }
            }
        }
    </script>
    @endpush
</x-layout.app>