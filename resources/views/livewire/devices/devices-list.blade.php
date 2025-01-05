<div class="mt-6 space-y-6">
    @if ($devices->count() > 0)
        @foreach ($devices as $device)
            <div
                class="my-4 px-6 py-4 bg-white rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-100 transition duration-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="p-4 leading-normal">
                    <div class="mb-2">
                        <span class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $device->model_name }} {{ $device->model_number }}
                        </span>
                    </div>
                    <div class="flex space-x-6 text-gray-600 dark:text-gray-400">
                        <div>
                            <strong class="text-sm">Manufacturer:</strong>
                            <p class="text-xs">{{ $device->manufacturer }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Brand:</strong>
                            <p class="text-xs">{{ $device->brand }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Description:</strong>
                            <p class="text-xs">{{ $device->description }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex space-x-6 text-gray-600 dark:text-gray-400">
                        {{-- <div><strong class="text-sm">Color:</strong>
                            <p class="text-xs">{{ $device->color }}</p>
                        </div> --}}
                    </div>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <div class="flex space-x-6">
                        <x-primary-button wire:click="$dispatch('edit-device', {id: {{ $device->id }}})"
                            class="bg-green-500 hover:bg-green-600 text-white">
                            Edit
                        </x-primary-button>
                        <x-primary-button wire:click="delete({{ $device->id }})"
                            wire:confirm="Are you sure you want to delete this device?"
                            class="bg-red-500 hover:bg-red-600 text-white">
                            Delete
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @endforeach
        {{ $devices->links() }}
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No devices found.
        </div>
    @endif
</div>
