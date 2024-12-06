<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $title;
    public string $slug;
    public string $description ;
    public string $status;
    public string $priority;
    public string $deadline;


    public function save()
    {

        // auth()->user()->tasks()->create($this->only(['title','slug','description','status','prioty','deadline']));

        // $this->reset('title','slug','description','status','prioty','deadline');

        // $this->dispatch('task-saved');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Add Task') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('comment.') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">

        <div>
            <x-input-label for="update_title" :value="__('Title')" />
            <x-text-input wire:model="title" id="update_title" name="title" class="mt-1 block w-full"  />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_slug" :value="__('Slug')" />
            <x-text-input wire:model="slug" id="update_slug" name="slug" class="mt-1 block w-full"  />
            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_description" :value="__('Description')" />
            <x-text-input wire:model="description" id="update_description" name="description" class="mt-1 block w-full"  />
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_status" :value="__('Status')" />
            <x-text-input wire:model="status" id="update_status" name="status" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_prioty" :value="__('Prioty')" />
            <x-text-input wire:model="prioty" id="update_prioty" name="prioty" class="mt-1 block w-full"/>
            <x-input-error :messages="$errors->get('prioty')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_deadline" :value="__('Deadline')" />
            <x-text-input wire:model="deadline" id="update_deadline" name="deadline" class="mt-1 block w-full"/>
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
