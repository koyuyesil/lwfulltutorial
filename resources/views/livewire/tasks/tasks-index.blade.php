
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Add New Task') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">


        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="max-w-xl">
       
                

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Add New Task') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('comment.') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">

        <div>
            <x-input-label for="update_title" :value="__('Title')" />
            <x-text-input wire:model="title" id="update_title" name="fname" type="text" class="mt-1 block w-full"  />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_slug" :value="__('Slug')" />
            <x-text-input wire:model="slug" id="update_slug" name="lname" type="text" class="mt-1 block w-full"  />
            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_description" :value="__('Description')" />
            <x-text-input wire:model="description" id="update_description" name="given-name" type="text" class="mt-1 block w-full"  />
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_status" :value="__('Status')" />
            {{-- <x-text-input wire:model="status" id="update_status" name="given-name" type="text" class="mt-1 block w-full" /> --}}
            <select wire:model="status" name="" id="status">
                @foreach ( $statusTypes as $status)
                    <option value="{{ $status->value }}">{{ $status->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_priority" :value="__('priority')" />
            {{-- <x-text-input wire:model="priority" id="update_priority" name="priority" type="text" class="mt-1 block w-full"/> --}}
            <select wire:model="priority" name="" id="priority">
                @foreach ( $priorityTypes as $priority)
                    <option value="{{ $priority->value }}">{{ $priority->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('priority')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_deadline" :value="__('Deadline')" />
            <x-text-input wire:model="deadline" id="update_deadline" name="deadline" type="date" class="mt-1 block w-full"/>
            <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="task-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>




            </div>
        </div>


    </div>
</div>




