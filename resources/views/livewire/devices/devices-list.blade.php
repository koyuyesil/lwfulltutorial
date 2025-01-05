<div class="mt-6">
    @if ($devices->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-xs font-semibold uppercase text-gray-700 dark:text-gray-300">
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Model</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Manufacturer</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Brand</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Description</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $device)
                        <tr class="text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $device->model_name }} {{ $device->model_number }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $device->manufacturer }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $device->brand }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $device->description }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">
                                <div class="flex justify-center space-x-4">
                                    <x-primary-button wire:click="$dispatch('edit-device', {id: {{ $device->id }}})"
                                        class="bg-green-500 hover:bg-green-600 text-white text-xs px-2 py-1">
                                        Edit
                                    </x-primary-button>
                                    <x-primary-button wire:click="delete({{ $device->id }})"
                                        wire:confirm="Are you sure you want to delete this device?"
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1">
                                        Delete
                                    </x-primary-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $devices->links() }}
        </div>
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No devices found.
        </div>
    @endif
</div>
