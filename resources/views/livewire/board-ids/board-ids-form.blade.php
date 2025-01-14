<div>
    <form wire:submit="save" class="mt-6 space-y-4">
        <!-- build_name -->
        <div class="mb-4">
            <x-input-label for="build_name" :value="__('build_name')" class="text-sm" />
            <x-text-input wire:model.live="form.build_name" id="build_name" name="build_name" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.build_name')" class="mt-1 text-sm" />
        </div>

       {{--  <!-- Brand -->
        <div class="mb-4">
            <x-input-label for="brand" :value="__('Brand')" class="text-sm" />
            <x-text-input wire:model.live="form.brand" id="brand" name="brand" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.brand')" class="mt-1 text-sm" />
        </div>

        <!-- Model Name -->
        <div class="mb-4">
            <x-input-label for="model_name" :value="__('Model Name')" class="text-sm" />
            <x-text-input wire:model.live="form.model_name" id="model_name" name="model_name" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.model_name')" class="mt-1 text-sm" />
        </div>

        <!-- Model Number -->
        <div class="mb-4">
            <x-input-label for="model_number" :value="__('Model Number')" class="text-sm" />
            <x-text-input wire:model.live="form.model_number" id="model_number" name="model_number" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.model_number')" class="mt-1 text-sm" />
        </div>

        <!-- Description -->
        <div class="mb-4">
            <x-input-label for="description" :value="__('Description')" class="text-sm" />
            <x-text-input wire:model.live="form.description" id="description" name="description" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.description')" class="mt-1 text-sm" />
        </div>
 --}}
        <!-- Submit Button -->
        <div class="mb-3 flex justify-between">
            <button type="submit"
                class="flex py-1 px-3 bg-indigo-500 hover:bg-indigo-600 text-sm text-white rounded-md">
                Submit
                <div wire:loading>
                    <x-koyu-svg-loading/>
                </div>
            </button>
            <button type="button" wire:click="refresh"
                class="flex py-1 px-3 bg-slate-400 hover:bg-slate-500 text-sm text-white rounded-md">Refresh
            </button>
        </div>

        <!-- Success Message -->
        <div>
            @if (session()->has('message'))
            <div class="p-2 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <span class="font-medium">{{ session('message') }}</span>
            </div>
            @endif
        </div>
    </form>
</div>

