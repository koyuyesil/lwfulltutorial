<div class="mt-6 space-y-6">
    @if ($customers->count() > 0)
        @foreach ($customers as $customer)
            <div
                class="my-4 px-6 py-4 bg-white rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-100 transition duration-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="p-4 leading-normal">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $customer->fname }} {{ $customer->lname }}
                        </h5>
                        <span
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-gray-100 rounded-full border border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                            {{ $customer->email }}
                        </span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-2">{{ $customer->address }}</p>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
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
