<header class="shadow-lg rounded-b-lg sticky bg-base-200 top-0 z-50 mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-4">
                <button x-show="view !== 'list'" @click="changeView('list')"
                    class="btn btn-ghost btn-circle">
                    <i class="fas fa-arrow-left text-lg"></i>
                </button>
                <h1 class="text-xl font-semibold" x-text="getPageTitle()"></h1>
            </div>
            <div x-show="view === 'list'" class="flex items-center space-x-3">
                <button @click="changeView('create')" class="btn btn-primary flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>New Bill</span>
                </button>
            </div>
        </div>
    </div>
</header>
