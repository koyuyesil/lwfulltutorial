<div>
    <form wire:submit="save" class="mt-6 space-y-4">
        <!-- build_name -->
        <div class="mb-4">
            <x-input-label for="build_name" :value="__('Build Name')" class="text-sm" />
            <x-text-input wire:model.live="form.build_name" id="build_name" name="build_name" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.build_name')" class="mt-1 text-sm" />
        </div>

        <!-- Mass-Production HWID -->
        <div class="mb-4">
            <x-input-label for="mass_production_hwid" :value="__('Mass-Production HWID')" class="text-sm" />
            <x-text-input wire:model.live="form.mass_production_hwid" id="mass_production_hwid"
                name="mass_production_hwid" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.mass_production_hwid')" class="mt-1 text-sm" />
        </div>

        <!-- Pre-Production HWID -->
        <div class="mb-4">
            <x-input-label for="pre_production_hwid" :value="__('Pre-Production HWID')" class="text-sm" />
            <x-text-input wire:model.live="form.pre_production_hwid" id="pre_production_hwid" name="pre_production_hwid"
                type="text" class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.pre_production_hwid')" class="mt-1 text-sm" />
        </div>

        <!-- Repair Methods -->
        {{--         <div class="mb-4">
            <x-input-label for="repair_methods" :value="__('Repair Methods')" class="text-sm" />
            <x-text-input wire:model.live="form.repair_methods" id="repair_methods" name="repair_methods" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.repair_methods')" class="mt-1 text-sm" />
        </div> --}}

        <!-- Repair Methods -->
        <div class="mb-4">
            <x-input-label for="repair_methods" :value="__('Repair Methods')" class="text-sm" />
            <x-text-input wire:model.live="form.repair_methods" id="repair_methods" name="repair_methods" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <div class="mt-1">
                @foreach (App\Enums\RepairMethod::cases() as $method)
                    <label class="inline-flex items-center mr-4">
                        <input type="checkbox" value="{{ $method->value }}"
                            class="repair-method-checkbox form-checkbox text-indigo-600"
                            data-method="{{ $method->value }}" onchange="updateRepairMethods()">
                        <span class="ml-2 text-sm">{{ $method->name }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('form.repair_methods')" class="mt-1 text-sm" />
        </div>


        <!-- Resistances -->
        <div class="mb-4">
            <x-input-label for="resistances" :value="__('Resistances')" class="text-sm" />
            <x-text-input wire:model.live="form.resistances" id="resistances" name="resistances" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.resistances')" class="mt-1 text-sm" />
        </div>

        <!-- Description -->
        <div class="mb-4">
            <x-input-label for="description" :value="__('Description')" class="text-sm" />
            <x-text-input wire:model.live="form.description" id="description" name="description" type="text"
                class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md" />
            <x-input-error :messages="$errors->get('form.description')" class="mt-1 text-sm" />
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
@push('scripts')
    <script>
        function updateRepairMethods() {
            // Seçilen checkbox'ları al
            const selectedMethods = [];

            // Tüm checkbox'ları döngü ile kontrol et
            document.querySelectorAll('.repair-method-checkbox:checked').forEach((checkbox) => {
                selectedMethods.push(checkbox.value);
            });

            // Seçilenleri virgülle ayırarak Livewire bileşenine gönder
            @this.set('form.repair_methods', selectedMethods.join(','));
        }
    </script>
@endpush
