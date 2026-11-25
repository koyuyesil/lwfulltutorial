<?php

namespace App\Livewire\Clients;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Müşteri kartı paneli: iletişim, cihazlar ve servis geçmişi.
 * Panel (drawer) üst sayfada durur; bu bileşen yalnızca içeriği yükler.
 */
class ClientsDetail extends Component
{
    public const AVATAR_COLORS = ['bg-sky-500', 'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-teal-500', 'bg-fuchsia-500'];

    public ?int $clientId = null;

    #[On('show-client')]
    public function load(int $id): void
    {
        $this->clientId = Auth::user()->clients()->findOrFail($id)->id;
    }

    #[On('client-saved')]
    public function refresh(): void
    {
        // Düzenlemeden sonra kart güncel veriyle yeniden çizilir
    }

    #[On('client-deleted')]
    public function clear(int $id): void
    {
        if ($this->clientId === $id) {
            $this->clientId = null;
            $this->dispatch('close-drawer', name: 'client-detail');
        }
    }

    public function render()
    {
        $client = $this->clientId
            ? Auth::user()->clients()->with([
                'clientsProducts.product',
                'clientsProducts.tickets' => fn ($q) => $q->latest('id'),
            ])->find($this->clientId)
            : null;

        $contacts = app(\App\Services\ClientContacts::class);

        return view('livewire.clients.clients-detail', [
            'client' => $client,
            'avatarClass' => $client ? self::AVATAR_COLORS[$client->id % count(self::AVATAR_COLORS)] : null,
            // QR için her sürümün vCard metni (sürüm değiştirince sunucuya gidilmez)
            'vcards' => $client
                ? collect(\App\Services\ClientContacts::VCARD_VERSIONS)->mapWithKeys(fn ($v) => [$v => $contacts->vcard($client, $v)])->all()
                : [],
            'vcardVersion' => (string) Auth::user()->setting('clients.vcard_version'),
        ]);
    }
}
