<div>
    @foreach ($tickets as $ticket)
        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-md mb-2">
            {{-- Cihaz ve Müşteri Bilgileri --}}
            <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                <span class="mr-2 font-medium text-gray-900 dark:text-white">
                    {{ $ticket->clientProduct->client->company ? $ticket->clientProduct->client->company : $ticket->clientProduct->client->fname . ' ' . $ticket->clientProduct->client->lname }}
                </span>
                <span class="mr-2">{{ $ticket->clientProduct->client->phone }}</span>
                <span class="mr-2">{{ $ticket->clientProduct->client->email }}</span>
            </div>

            {{-- Cihaz Bilgisi --}}
            <div class="flex items-center text-xs text-gray-600 dark:text-gray-400 mt-1">
                <span class="mr-2">Product: {{ $ticket->clientProduct->product->brand }}
                    {{ $ticket->clientProduct->product->model_name }}</span>
            </div>

            {{-- Ticket Bilgileri --}}
            <div class="flex items-center text-xs text-gray-600 dark:text-gray-400 mt-1">
                <span class="mr-2"><strong>Ticket #{{ $ticket->id }}</strong></span>
                <span class="mr-2">Status: {{ $ticket->status }}</span>
                <span class="mr-2">Issue: {{ $ticket->problem }}</span>
                <span>Created At: {{ $ticket->created_at }}</span>
            </div>
            <div class="mt-3 flex justify-between">
                <div>
                    @foreach (App\Enums\StatusType::cases() as $case)
                        <button type="button" wire:click="changeStatus({{ $ticket->id }}, '{{ $case->value }}')"
                            @class([
                                'inline-flex items-center px-4 py-2 bg-white border rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150',
                                $case->color() => true, //enumlar kadar buton üretilir true ise sitil gelmesi yeterli demek
                            ]) {{ $case->value == $ticket->status->value ? 'disabled' : '' }}>
                            {{ Str::of($case->value)->headline() }}
                        </button>
                    @endforeach
                </div>
                <div class="flex justify-between items-center">
                    <button type="button" wire:click="$dispatch('edit', {id: {{ $ticket->id }}})"
                        class="flex items-center text-teal-700 hover:text-white border border-teal-700 hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-4 py-2  me-2 mb-2 dark:border-teal-500 dark:text-teal-500 dark:hover:text-white dark:hover:bg-teal-600 dark:focus:ring-teal-900"

                        <svg class="w-4 h-4 me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10.5 3.75a6 6 0 0 0-5.98 6.496A5.25 5.25 0 0 0 6.75 20.25H18a4.5 4.5 0 0 0 2.206-8.423 3.75 3.75 0 0 0-4.133-4.303A6.001 6.001 0 0 0 10.5 3.75Zm2.03 5.47a.75.75 0 0 0-1.06 0l-3 3a.75.75 0 1 0 1.06 1.06l1.72-1.72v4.94a.75.75 0 0 0 1.5 0v-4.94l1.72 1.72a.75.75 0 1 0 1.06-1.06l-3-3Z"
                                clip-rule="evenodd" />
                        </svg>
                        Edit
                    </button>
                    <button type="button" wire:click="delete({{ $ticket->id }})"
                        wire:confirm="Are you sure you want to delete this ticket?"
                        class="flex items-center text-red-700 hover:text-white border border-red-600 hover:bg-red-700 shadow-sm hover:shadow-lg focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2  me-2 mb-2 dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <svg class="w-4 h-4 me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10.5 3.75a6 6 0 0 0-5.98 6.496A5.25 5.25 0 0 0 6.75 20.25H18a4.5 4.5 0 0 0 2.206-8.423 3.75 3.75 0 0 0-4.133-4.303A6.001 6.001 0 0 0 10.5 3.75Zm2.03 5.47a.75.75 0 0 0-1.06 0l-3 3a.75.75 0 1 0 1.06 1.06l1.72-1.72v4.94a.75.75 0 0 0 1.5 0v-4.94l1.72 1.72a.75.75 0 1 0 1.06-1.06l-3-3Z"
                                clip-rule="evenodd" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
{{-- Pagination --}}
<div class="mt-4">
    {{ $tickets->links() }}
</div>
</div>
