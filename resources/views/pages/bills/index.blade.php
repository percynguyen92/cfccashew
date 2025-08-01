<!-- resources/views/pages/bills/index.blade.php -->
@extends('layouts.master')

@section('title', 'Quản lý thông tin điều thô')

@section('content')
    <div x-data="billsManager()" x-init="init()">
        
        <!-- Search Section -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <x-bills.bill-search />
            </div>
        </div>
        
        <!-- Bills List Section -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="card-title text-2xl">Danh sách Bills</h2>
                    <button @click="openCreateModal()" 
                            class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Thêm Bill
                    </button>
                </div>
                
                <x-bills.bill-list />
            </div>
        </div>
        
        <!-- Modals -->
        <x-bills.bill-modal-form />
        <x-shared.confirm-modal />
    </div>

    @push('scripts')
    <script>
        function billsManager() {
            return {
                bills: [],
                pagination: {},
                search: '',
                loading: false,
                selectedBill: null,
                
                async init() {
                    await this.loadBills();
                },
                
                async loadBills(page = 1) {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/v1/bills?page=${page}&search=${this.search}`);
                        const data = await response.json();
                        this.bills = data.data;
                        this.pagination = data.meta;
                    } catch (error) {
                        console.error('Error loading bills:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async search() {
                    await this.loadBills(1);
                },
                
                openCreateModal() {
                    this.selectedBill = null;
                    this.$dispatch('open-bill-modal');
                },
                
                editBill(bill) {
                    this.selectedBill = bill;
                    this.$dispatch('open-bill-modal');
                },
                
                async deleteBill(billId) {
                    if (await this.confirmDelete()) {
                        try {
                            await fetch(`/api/v1/bills/${billId}`, { method: 'DELETE' });
                            await this.loadBills();
                            this.showAlert('Bill đã được xóa thành công', 'success');
                        } catch (error) {
                            this.showAlert('Có lỗi xảy ra khi xóa bill', 'error');
                        }
                    }
                },
                
                viewBillDetail(billId) {
                    window.location.href = `/bills/${billId}`;
                }
            }
        }
    </script>
    @endpush
@endsection
