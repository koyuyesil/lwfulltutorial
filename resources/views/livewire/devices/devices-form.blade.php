<div>
    <form wire:submit="save" class="mt-6 space-y-4">
        <!-- Manufacturer -->
        <div>
            <x-input-label for="manufacturer" :value="__('Manufacturer')" class="text-sm" />
            <x-text-input wire:model.live="form.manufacturer" id="manufacturer" name="manufacturer" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.manufacturer')" class="mt-1 text-sm" />
        </div>

        <!-- Brand -->
        <div>
            <x-input-label for="brand" :value="__('Brand')" class="text-sm" />
            <x-text-input wire:model.live="form.brand" id="brand" name="brand" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.brand')" class="mt-1 text-sm" />
        </div>

        <!-- Model Name -->
        <div>
            <x-input-label for="model_name" :value="__('Model Name')" class="text-sm" />
            <x-text-input wire:model.live="form.model_name" id="model_name" name="model_name" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.model_name')" class="mt-1 text-sm" />
        </div>

        <!-- Model Number -->
        <div>
            <x-input-label for="model_number" :value="__('Model Number')" class="text-sm" />
            <x-text-input wire:model.live="form.model_number" id="model_number" name="model_number" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.model_number')" class="mt-1 text-sm" />
        </div>

        <!-- Description -->
        <div>
            <x-input-label for="description" :value="__('Description')" class="text-sm" />
            <x-text-input wire:model.live="form.description" id="description" name="description" type="text"
                class="mt-1 block w-full text-sm py-1 px-2" />
            <x-input-error :messages="$errors->get('form.description')" class="mt-1 text-sm" />
        </div>

        <!-- Submit Button -->
        <div class="mb-3 flex justify-between">
            <button type="submit"
                class="flex py-1 px-3 bg-indigo-500 hover:bg-indigo-600 text-sm text-white rounded-md">
                Submit
                <div wire:loading>
                    <svg aria-hidden="true"
                        class="w-4 h-4 mx-2 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                        viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor" />
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill" />
                    </svg>
                    <span class="sr-only">Loading...</span>
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
