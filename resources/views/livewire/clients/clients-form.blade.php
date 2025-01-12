<div>
    <form wire:submit="save" class="mt-6 space-y-4">
        <!-- First Name -->
        <div>
            <x-input-label for="first_name" :value="__('First Name')" class="text-sm" />
            <x-text-input wire:model.live="form.first_name" id="first_name" name="first_name" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.first_name')" class="mt-1 text-sm" />
        </div>

        <!-- Last Name -->
        <div>
            <x-input-label for="last_name" :value="__('Last Name')" class="text-sm" />
            <x-text-input wire:model.live="form.last_name" id="last_name" name="last_name" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.last_name')" class="mt-1 text-sm" />
        </div>

        <!-- Company -->
        <div>
            <x-input-label for="company" :value="__('Company')" class="text-sm" />
            <x-text-input wire:model.live="form.company" id="company" name="company" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.company')" class="mt-1 text-sm" />
        </div>

        <!-- Phone -->
        <div>
            <x-input-label for="phone" :value="__('Phone')" class="text-sm" />
            <x-text-input wire:model.live="form.phone" id="phone" name="phone" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.phone')" class="mt-1 text-sm" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-sm" />
            <x-text-input wire:model.live="form.email" id="email" name="email" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-1 text-sm" />
        </div>

        <!-- Address -->
        <div>
            <x-input-label for="address" :value="__('Address')" class="text-sm" />
            <x-text-input wire:model.live="form.address" id="address" name="address" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.address')" class="mt-1 text-sm" />
        </div>

        <!-- Submit Button -->
        <div class="mb-3 flex justify-between">
            <button type="submit"
                class="flex py-1 px-3 bg-indigo-500 hover:bg-indigo-600 text-sm text-white rounded-md">
                Submit
                <div wire:loading>
                    <x-koyu-svg-loading />
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
