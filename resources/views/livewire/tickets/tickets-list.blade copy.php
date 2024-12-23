<div>
    @foreach ($customers as $customer)
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ $customer->company ?? $customer->fname . ' ' . $customer->lname }}
            </h3>

            {{-- Müşteri bilgileri --}}
            <p class="text-sm text-gray-600 dark:text-gray-400">Phone: {{ $customer->phone }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Email: {{ $customer->email }}</p>

            {{-- Cihazlar Listesi --}}
            @foreach ($customer->customerDevices as $customerDevice)
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                        Device: {{ $customerDevice->device->brand }} {{ $customerDevice->device->model_name }}
                    </h4>

                    {{-- Cihaza ait Ticket'lar --}}
                    @if ($customerDevice->ticket->count() > 0)
                        <ul>
                            @foreach ($customerDevice->ticket as $ticket)
                                <li class="mt-2">
                                    <strong>Ticket #{{ $ticket->id }}</strong> - Status: {{ $ticket->status }}<br>
                                    Issue: {{ $ticket->issue_description }}<br>
                                    Created At: {{ $ticket->created_at->format('d-m-Y') }}
                                </li>
                            @endforeach
                        </ul>

                    @else
                        <p>No tickets found for this device.</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
    {{ $customers->links() }}
</div>
