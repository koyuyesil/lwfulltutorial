<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Manage - Tickets') }}
    </h2>
</x-slot>
<div class="py-12">
    <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="grid grid-cols-5 gap-6"> {{-- Grid yapısı 4 sütun olarak ayarlandı --}}

                <!-- Tickets Section -->
                <section class="col-span-3 w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg"> {{-- Liste kısmı genişlik olarak 3/4 kaplıyor --}}
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Tickets') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('List of tickets') }}
                        </p>
                    </header>
                    <livewire:tickets.tickets-list lazy />
                </section>

                <!-- Add New Ticket Section -->
                <section class="col-span-2 w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg"> {{-- Ekleme kısmı genişlik olarak 1/4 kaplıyor --}}
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Add New Ticket') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Add a new ticket') }}
                        </p>
                    </header>
                    <livewire:tickets.tickets-form />
                </section>

            </div>
        </div>
    </div>
</div>