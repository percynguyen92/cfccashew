<!-- resources/views/components/ui/mobile-table.blade.php -->
@props(['headers', 'data'])

<div class="hidden md:block">
    <!-- Desktop Table View -->
    <div class="overflow-x-auto">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

<div class="md:hidden">
    <!-- Mobile Card View -->
    <div class="space-y-4">
        {{ $mobileSlot ?? $slot }}
    </div>
</div>