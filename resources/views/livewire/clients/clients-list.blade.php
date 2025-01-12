<div class="mt-6 space-y-6">
    @if ($clients->count() > 0)
        @foreach ($clients as $client)
            <div class="my-4 px-6 py-4 bg-white rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-100 transition duration-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="p-4 leading-normal">
                    <div class="mb-2">
                        <span class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $client->company ? $client->company : $client->first_name . ' ' . $client->last_name }}
                        </span>
                    </div>
                    <div class="flex space-x-6 text-gray-600 dark:text-gray-400">
                        <div>
                            <strong class="text-sm">Phone:</strong>
                            <p class="text-xs">{{ $client->phone }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Email:</strong>
                            <p class="text-xs">{{ $client->email }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">{{ $client->company ? 'Representative:':'Company:' }}</strong>
                            <p class="text-xs">{{ $client->company ? $client->first_name . ' ' . $client->last_name : $client->company }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex space-x-6 text-gray-600 dark:text-gray-400">
                        <div><strong class="text-sm">Address:</strong>
                            <p class="text-xs">{{ $client->address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer's products Section -->
                @if ($client->clientsProducts->count() > 0)
                    <div class="p-4 leading-normal mt-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">products</h3>
                        <ul class="list-disc pl-6">
                            @foreach ($client->clientsProducts as $clientProduct)
                                <li class="text-sm text-gray-700 dark:text-gray-300">
                                    <a href="#" class="hover:underline">
                                        Brand: {{ $clientProduct->product->brand }}&nbsp;&nbsp;Model: {{ $clientProduct->product->model_name }}&nbsp;&nbsp;Serial: {{ $clientProduct->serial }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No products listed for this client.
                    </div>
                @endif

                <div class="flex justify-between items-center mt-2">
                    <div class="flex space-x-6">
                        <x-primary-button wire:click="$dispatch('edit', {id: {{ $client->id }}})"
                            class="bg-green-500 hover:bg-green-600 text-white">
                            Edit
                        </x-primary-button>
                        <x-primary-button wire:click="delete({{ $client->id }})"
                            wire:confirm="Are you sure you want to delete this customer?"
                            class="bg-red-500 hover:bg-red-600 text-white">
                            Delete
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @endforeach
        {{ $clients->links() }}
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No clients found.
        </div>
    @endif
</div>
