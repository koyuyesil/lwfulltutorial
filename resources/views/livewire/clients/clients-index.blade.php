<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Manage - Clients') }}
    </h2>
</x-slot>
<div class="py-12 flex justify-center">
    <div class="grid grid-cols-5 gap-6 p-6 bg-white dark:bg-gray-800 shadow rounded-lg max-w-6xl">
        <!-- Clients Section -->
        <section class="col-span-4 w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            {{-- Liste kısmı genişlik olarak 1/2 kaplıyor --}}
            <header>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Clients') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('The Client List is the core database of customer details, linked to service records, device models, and customer devices for efficient management.') }}
                </p>
            </header>
            <livewire:clients.clients-list lazy />
        </section>

        <!-- Add New Client Section -->
        <section class="col-span-1 w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            {{-- Ekleme kısmı genişlik olarak 1/2 kaplıyor --}}
            <header>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Add New Client') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('All fields are mandatory to ensure complete and accurate records.') }}
                </p>
            </header>
            <livewire:clients.clients-form />
        </section>
    </div>
</div>
