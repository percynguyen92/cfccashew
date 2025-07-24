<x-layout title="Bills Management System">
    <div class="min-h-screen flex flex-col bg-gradient-to-r from-cyan-500 to-blue-500">
        <!-- Main -->
        <main class="max-w-6xl mx-auto">
            <x-bills.header />
            <x-bills.global-error />
            <x-bills.loading-overlay />
            <x-bills.list />
            <x-bills.form />
            <x-bills.detail />
            <x-bills.delete-modal />
        </main>
    </div>
</x-layout>
