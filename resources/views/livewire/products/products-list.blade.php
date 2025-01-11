<div class="mt-6">
    @if ($products->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-xs font-semibold uppercase text-gray-700 dark:text-gray-300">

                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Manufacturer</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Brand</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Model</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Model No</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Description</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $product->manufacturer }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $product->brand }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $product->model_name }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $product->model_number }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $product->description }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">
                                <div class="flex justify-center space-x-4">
                                    <button wire:click="$dispatch('edit-product', {id: {{ $product->id }}})"
                                        class="flex py-1 px-3 bg-orange-500 hover:bg-red-600 text-sm text-white rounded-md">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $product->id }})"
                                        wire:confirm="Are you sure you want to delete this device?"
                                        class="flex py-1 px-3 bg-green-500 hover:bg-green-400 text-sm text-white rounded-md">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No products found.
        </div>
    @endif
</div>
