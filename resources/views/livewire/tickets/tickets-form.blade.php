<div>
    {{-- Müşteri Ekleme Alanı --}}
    <h3>Yeni Müşteri ve Cihaz Ekle</h3>
    {{-- Müşteri ve Cihaz Seçim Alanları --}}
    <select wire:model.live="selectedCustomer">
        <option value="">Müşteri Seç</option>
        @foreach ($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->fname }}</option>
        @endforeach
    </select>
    <select wire:model.live="selectedDevice">
        <option value="">Cihaz Seç</option>
        @foreach ($devices as $device)
            <option value="{{ $device->id }}">{{ $device->model }} ({{ $device->serial_number }})</option>
        @endforeach
    </select>
    <form wire:submit="createCustomerAndDevice">
        <input type="text" wire:model.live="newCustomerFirstName" placeholder="Müşteri Adı">
        <input type="text" wire:model.live="newCustomerLastName" placeholder="Müşteri Soydı">
        <input type="text" wire:model.live="newCustomerCompany" placeholder="Şirket">
        <input type="text" wire:model.live="newCustomerPhone" placeholder="Telefon">
        <input type="text" wire:model.live="newCustomerEmail" placeholder="Mail">
        <input type="text" wire:model.live="newCustomerAdress" placeholder="Adres">
        <button type="submit">Kaydet</button>
    </form>
    {{-- Mevcut Arıza Kayıtları --}}
    <h3>Mevcut Arıza Kayıtları</h3>
    <ul>
        @foreach ($tickets as $ticket)
            <li>
                {{ $ticket->problem }} - {{ $ticket->status }}
                ({{ $ticket->customer->name }} / {{ $ticket->device->model }})
            </li>
        @endforeach
    </ul>
    {{-- Arıza Detayları --}}
    <h3>Arıza Kaydı</h3>
    <textarea wire:model="problem" placeholder="Arıza Detayı"></textarea></br>
    <button type="button" wire:click="createTicket">Arıza Kaydı Oluştur</button>
</div>
