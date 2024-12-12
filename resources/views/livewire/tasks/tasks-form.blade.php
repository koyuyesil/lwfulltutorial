<div>
    <form wire:submit="save" class="mt-6 space-y-6">
        <div>
            <x-input-label for="update_title" :value="__('Title')" />
            <x-text-input wire:model.live="form.title" id="update_title" name="fname" type="text"
                class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('form.title')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="update_slug" :value="__('Slug')" />
            <x-text-input wire:model.live="form.slug" id="update_slug" name="lname" type="text"
                class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('form.slug')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="update_description" :value="__('Description')" />
            <x-text-input wire:model.live="form.description" id="update_description" name="given-name" type="text"
                class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="update_status" :value="__('Status')" />
            {{-- <x-text-input wire:model.live="status" id="update_status" name="given-name" type="text" class="mt-1 block w-full" /> --}}
            <select wire:model.live="form.status" name="" id="status">
                @foreach (\App\Enums\StatusType::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('form.status')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="update_priority" :value="__('priority')" />
            {{-- <x-text-input wire:model.live="priority" id="update_priority" name="priority" type="text" class="mt-1 block w-full"/> --}}
            <select wire:model.live="form.priority" name="" id="priority">
                @foreach (\App\Enums\PriorityType::cases() as $priority)
                    <option value="{{ $priority->value }}">{{ $priority->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('form.priority')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="update_deadline" :value="__('Deadline')" />
            <x-text-input wire:model.live="form.deadline" id="update_deadline" name="deadline" type="date"
                class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('form.deadline')" class="mt-2" />
        </div>

        <div class="mb-3 flex justify-between">
            <button type="submit"
                class="flex py-2 px-4 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md">
                Submit
                <div wire:loading>
                    <svg aria-hidden="true"
                        class="w-5 h-5 mx-2 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
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
                class="flex py-2 px-4 bg-slate-400 hover:bg-slate-500 text-white rounded-md">Refresh
            </button>
        </div>
        <div>
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </form>
    @if (session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
        role="alert">
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
</div>
