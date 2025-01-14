<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Manage - Board Id List') }}
    </h2>
</x-slot>
<div class="py-12 flex justify-center">
    <div class="grid grid-cols-5 gap-6 p-6 bg-white dark:bg-gray-800 shadow rounded-lg max-w-6xl">
        <!-- Devices Section -->
        <section class="col-span-4 w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            {{-- Liste kısmı genişlik olarak 1/2 kaplıyor --}}
            <header>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Board Id List') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('This is the base repository for all fundamental device models required for creating new service records. Only devices added here can be selected for service entries.') }}
                </p>
            </header>
            <livewire:board-ids.board-ids-list lazy />
        </section>

        <!-- Add New Device Section -->
        <section class="col-span-1 w-full mt-2 p-4 space-y-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            {{-- Ekleme kısmı genişlik olarak 1/2 kaplıyor --}}
            <header>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Add Board Id') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('All fields are mandatory to ensure complete and accurate data for each device.') }}
                </p>
            </header>
            <livewire:board-ids.board-ids-form />
        </section>
    </div>
</div>
