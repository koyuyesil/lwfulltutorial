<div>
    {{-- Müşteri Ekleme Alanı --}}
    <h3>Yeni Kayıt Ekle</h3>

    {{-- Müşteri Seçim Alanı --}}
    <select wire:model.live="selectedCustomer">
        <option value="">Müşteri Seç</option>
        @foreach ($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->fname }} {{ $customer->lname }}</option>
        @endforeach
    </select>
    {{-- Cihaz Seçim Alanı --}}
    <select wire:model.live="selectedDevice">
        <option value="">Cihaz Seç</option>
        @foreach ($devices as $device)
            <option value="{{ $device->id }}">{{ $device->model_name }} ({{ $device->model_number }})</option>
        @endforeach
    </select>

    {{-- Yeni Müşteri Ekleme Formu --}}
    <form wire:submit.prevent="createCustomerAndDevice">
        <input type="text" wire:model.live="fname" placeholder="Müşteri Adı">
        <input type="text" wire:model.live="lname" placeholder="Müşteri Soyadı">
        <input type="text" wire:model.live="company" placeholder="Şirket">
        <input type="text" wire:model.live="phone" placeholder="Telefon">
        <input type="email" wire:model.live="email" placeholder="Mail">
        <input type="text" wire:model.live="address" placeholder="Adres">

        {{-- Cihaz Bilgileri --}}
        <h3>Cihaz Bilgileri</h3>
        <input type="text" wire:model.live="manufacturer" placeholder="Üretici">
        <input type="text" wire:model.live="brand" placeholder="Marka">
        <input type="text" wire:model.live="model_name" placeholder="Model Adı">
        <input type="text" wire:model.live="model_number" placeholder="Model Numarası">
        <input type="text" wire:model.live="description" placeholder="Açıklama">
        <input type="text" wire:model.live="serial" placeholder="Serial">
        <input type="text" wire:model.live="imei" placeholder="IMEI">
        <input type="text" wire:model.live="color" placeholder="Renk">

        <button type="submit">Kaydet</button>
    </form>

    {{-- Arıza Detayları --}}
    <h3>Arıza Kaydı</h3>
    <textarea wire:model="problem" placeholder="Arıza Detayı"></textarea></br>
    <select wire:model="priority">
        <option value="">Öncelik Seç</option>
        <option value="low">Düşük</option>
        <option value="medium">Orta</option>
        <option value="high">Yüksek</option>
    </select>
    <button type="button" wire:click="createTicket">Arıza Kaydı Oluştur</button>
</div>
