<div>
    @foreach ($tickets as $ticket)
        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-md mb-2">
            {{-- Cihaz ve Müşteri Bilgileri --}}
            <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                <span class="mr-2 font-medium text-gray-900 dark:text-white">
                    {{ $ticket->customerDevice->customer->company ?? $ticket->customerDevice->customer->fname . ' ' . $ticket->customerDevice->customer->lname }}
                </span>
                <span class="mr-2">{{ $ticket->customerDevice->customer->phone }}</span>
                <span class="mr-2">{{ $ticket->customerDevice->customer->email }}</span>
            </div>

            {{-- Cihaz Bilgisi --}}
            <div class="flex items-center text-xs text-gray-600 dark:text-gray-400 mt-1">
                <span class="mr-2">Device: {{ $ticket->customerDevice->device->brand }} {{ $ticket->customerDevice->device->model_name }}</span>
            </div>

            {{-- Ticket Bilgileri --}}
            <div class="flex items-center text-xs text-gray-600 dark:text-gray-400 mt-1">
                <span class="mr-2"><strong>Ticket #{{ $ticket->id }}</strong></span>
                <span class="mr-2">Status: {{ $ticket->status }}</span>
                <span class="mr-2">Issue: {{ $ticket->issue_description }}</span>
                <span>Created At: {{ $ticket->created_at->format('d-m-Y') }}</span>
            </div>
        </div>
    @endforeach

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
