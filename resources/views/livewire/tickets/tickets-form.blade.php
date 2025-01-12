<div>
    {{-- Müşteri Ekleme Alanı --}}
    <h3 class=" mt-4 text-md font-semibold">Mevcut Kayıttan Seç</h3>

    {{-- Müşteri Seçim Alanı --}}
    <div>
        <x-input-label for="selectedClient" :value="__('Müşteri Seç')" class="text-sm" />
        <select wire:model.live="selectedClient" id="selectedClient"
            class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md">
            <option value="">Müşteri Seç</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('selectedClient')" class="mt-1 text-sm" />
    </div>

    {{-- Cihaz Seçim Alanı --}}
    <div>
        <x-input-label for="selectedProduct" :value="__('Cihaz Seç')" class="text-sm" />
        <select wire:model.live="selectedProduct" id="selectedProduct"
            class="mt-1 block w-full text-sm py-1 px-2 border-gray-300 rounded-md">
            <option value="">Cihaz Seç</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}">{{ $product->model_name }} ({{ $product->model_number }})</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('selectedProduct')" class="mt-1 text-sm" />
    </div>

    {{-- TODO FORM ERROR FIELDS --}}
    {{-- Yeni Müşteri Ekleme Formu --}}
    <form wire:submit.prevent="createClientAndProduct" class="mt-4 space-y-4">
        <h3 class="text-md font-semibold">Müşteri Bilgileri</h3>
        <x-text-input wire:model.live="first_name" id="first_name" placeholder="Müşteri Adı"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="last_name" id="last_name" placeholder="Müşteri Soyadı"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="company" id="company" placeholder="Şirket"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="phone" id="phone" placeholder="Telefon"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="email" id="email" placeholder="Mail"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="address" id="address" placeholder="Adres"
            class="block w-full text-sm py-1 px-2" />

        <h3 class="text-md font-semibold">Cihaz Bilgileri</h3>
        <x-text-input wire:model.live="manufacturer" id="manufacturer" placeholder="Üretici"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="brand" id="brand" placeholder="Marka"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="model_name" id="model_name" placeholder="Model Adı"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="model_number" id="model_number" placeholder="Model Numarası"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="description" id="description" placeholder="Açıklama"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="serial" id="serial" placeholder="Serial"
            class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="imei" id="imei" placeholder="IMEI" class="block w-full text-sm py-1 px-2" />
        <x-text-input wire:model.live="color" id="color" placeholder="Renk"
            class="block w-full text-sm py-1 px-2" />

        <div class="flex justify-between">
            <button type="submit"
                class="flex py-1 px-3 bg-indigo-500 hover:bg-indigo-600 text-sm text-white rounded-md">
                Kaydet
                <div wire:loading>
                    <x-koyu-svg-loading />
                </div>
            </button>
        </div>
    </form>

    {{-- Arıza Detayları --}}
    <h3 class="text-md font-semibold mt-6">Arıza Kaydı</h3>
    <textarea wire:model="problem" placeholder="Arıza Detayı"
        class="block w-full text-sm py-1 px-2 border-gray-300 rounded-md"></textarea>
    <select wire:model="priority" class="mt-2 block w-full text-sm py-1 px-2 border-gray-300 rounded-md">
        @foreach (\App\Enums\PriorityType::cases() as $priority)
            <option value="{{ $priority->value }}">{{ $priority->name }}</option>
        @endforeach
    </select>
    <select wire:model="status" class="mt-2 block w-full text-sm py-1 px-2 border-gray-300 rounded-md">
        @foreach (\App\Enums\StatusType::cases() as $status)
            <option value="{{ $status->value }}">{{ $status->name }}</option>
        @endforeach
    </select>
    <div class="flex justify-between mt-4">
        <button type="button" wire:click="create"
            class="py-1 px-3 bg-green-500 hover:bg-green-600 text-sm text-white rounded-md">
            Arıza Kaydı Oluştur
        </button>
    </div>
</div>
