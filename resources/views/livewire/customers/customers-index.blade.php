<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Manage - Customers') }}
    </h2>
</x-slot>
<div class="py-12">
    <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{-- grid kullanarak iki eşit sütun yapıyoruz --}}

                <!-- Customers Section -->
                <section class="w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Customers') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('List of customers') }}
                        </p>
                    </header>
                    <livewire:customers.customers-list lazy />
                </section>

                <!-- Add New Task Section -->
                <section class="w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Add New Customer') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Add a new customer') }}
                        </p>
                    </header>
                    <livewire:customers.customers-form />
                </section>

            </div>
        </div>
    </div>
</div>
