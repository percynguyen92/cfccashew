<!-- resources/views/components/containers/container-list.blade.php -->
<div class="overflow-x-auto">
    <table class="table table-zebra w-full">
        <thead>
            <tr>
                <th></th>
                <th>Truck</th>
                <th>Container Number</th>
                <th>Số bao</th>
                <th>Trọng lượng tổng</th>
                <th>Trọng lượng Net</th>
                <th>Outturn TB</th>
                <th class="text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="container in containers" :key="container.id">
                <template>
                    <!-- Main Container Row -->
                    <tr>
                        <td>
                            <button @click="toggleContainerTests(container.id)" 
                                    class="btn btn-ghost btn-xs">
                                <svg class="w-4 h-4 transition-transform" 
                                     :class="{ 'rotate-90': expandedContainers.includes(container.id) }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </td>
                        <td x-text="container.truck"></td>
                        <td x-text="container.container_number"></td>
                        <td x-text="container.quantity_of_bags"></td>
                        <td x-text="container.w_total + ' kg'"></td>
                        <td x-text="container.w_net + ' kg'"></td>
                        <td>
                            <div class="badge" 
                                 :class="container.avg_outturn_rate > 25 ? 'badge-success' : 'badge-warning'"
                                 x-text="container.avg_outturn_rate ? container.avg_outturn_rate + ' lbs/80kg' : 'N/A'">
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button @click="editContainer(container)" class="btn btn-ghost btn-xs">
                                    Sửa
                                </button>
                                <button @click="deleteContainer(container.id)" class="btn btn-ghost btn-xs text-error">
                                    Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Expanded Container Cutting Tests -->
                    <tr x-show="expandedContainers.includes(container.id)" x-transition>
                        <td colspan="8" class="bg-base-200 p-0">
                            <x-containers.container-cutting-tests :container-id="container.id" />
                        </td>
                    </tr>
                </template>
            </template>
        </tbody>
    </table>
</div>