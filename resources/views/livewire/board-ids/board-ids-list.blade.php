<div class="mt-6">
    @if ($boardIds->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-700">
                <thead>
                    <tr
                        class="bg-gray-100 dark:bg-gray-800 text-xs font-semibold uppercase text-gray-700 dark:text-gray-300">

                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Model</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Build Name</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Mass-Prod. HWID</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Pre-Prod. HWID</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Repair Methods</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Resistances</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Description</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($boardIds as $boardId)
                        <tr
                            class="text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->product->manufacturer }}
                                {{ $boardId->product->brand }}
                                {{ $boardId->product->model_name }}
                                {{ $boardId->product->model_number }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->build_name }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->mass_production_hwid }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->pre_production_hwid }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                @php
                                    $classes = [
                                        'meta' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                        'flash' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                        'patch' =>
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                        'unlocked' =>
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                        'locked' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                        'resistor' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
                                    ];
                                @endphp

                                @foreach ($boardId->repair_methods as $method)
                                    <span
                                        class="text-xs font-medium me-2 px-2.5 py-0.5 rounded {{ $classes[$method] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300' }}">
                                        {{ ucfirst($method) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->resistances }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->description }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">
                                <div class="flex justify-center space-x-4">
                                    <button wire:click="$dispatch('edit', {id: {{ $boardId->id }}})"
                                        class="flex py-1 px-3 bg-orange-500 hover:bg-red-600 text-sm text-white rounded-md">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $boardId->id }})"
                                        wire:confirm="Are you sure you want to delete this device?"
                                        class="flex py-1 px-3 bg-green-500 hover:bg-green-400 text-sm text-white rounded-md">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr
                            class="text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                Description
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $boardIds->links() }}
        </div>
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No boardIds found.
        </div>
    @endif
</div>
