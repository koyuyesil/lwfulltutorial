<div class="mt-6 space-y-6">
    @if ($customers->count() > 0)
        @foreach ($customers as $customer)
            <div class="my-4 px-6 py-4 bg-white rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-100 transition duration-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="p-4 leading-normal">
                    <div class="mb-2">
                        <span class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $customer->fname }} {{ $customer->lname }}
                        </span>
                    </div>
                    <div class="flex space-x-6 text-gray-600 dark:text-gray-400">
                        <div>
                            <strong class="text-sm">Phone:</strong>
                            <p class="text-xs">{{ $customer->phone }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Email:</strong>
                            <p class="text-xs">{{ $customer->email }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Company:</strong>
                            <p class="text-xs">{{ $customer->company }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex space-x-6 text-gray-600 dark:text-gray-400">
                        <div><strong class="text-sm">Address:</strong>
                            <p class="text-xs">{{ $customer->address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer's Devices Section -->
                @if ($customer->customerDevices->count() > 0)
                    <div class="mt-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Devices</h3>
                        <ul class="list-disc pl-6">
                            @foreach ($customer->customerDevices as $customerDevice)
                                <li class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $customerDevice->device->brand }} {{ $customerDevice->device->model_name }} - Serial: {{ $customerDevice->serial }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No devices listed for this customer.
                    </div>
                @endif

                <div class="flex justify-between items-center mt-2">
                    <div class="flex space-x-6">
                        <x-primary-button wire:click="$dispatch('edit', {id: {{ $customer->id }}})"
                            class="bg-green-500 hover:bg-green-600 text-white">
                            Edit
                        </x-primary-button>
                        <x-primary-button wire:click="delete({{ $customer->id }})"
                            wire:confirm="Are you sure you want to delete this customer?"
                            class="bg-red-500 hover:bg-red-600 text-white">
                            Delete
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @endforeach
        {{ $customers->links() }}
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No customers found.
        </div>
    @endif
</div>
