<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketEvent;
use App\Support\NotificationTemplates;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Müşteriye hazır şablonla bildirim: WhatsApp, SMS ya da e-posta uygulaması önceden doldurulmuş açılır.
 * Durum değişince öneri olarak öne çıkar; gönderimler zaman çizelgesine kaydedilir.
 */
class TicketNotify extends Component
{
    #[Locked]
    public int $ticketId;

    public string $template = '';

    public string $message = '';

    public bool $suggest = false;

    public function mount(int $ticketId): void
    {
        $this->ticketId = $ticketId;
        $this->template = $this->ticket()->status->value;
        $this->fillMessage();
    }

    protected function ticket(): Ticket
    {
        return Auth::user()->tickets()->with('clientProduct.client', 'clientProduct.product', 'items')->findOrFail($this->ticketId);
    }

    protected function fillMessage(): void
    {
        $status = TicketStatus::tryFrom($this->template) ?? $this->ticket()->status;
        $this->message = NotificationTemplates::for($this->ticket(), $status);
    }

    public function updatedTemplate(): void
    {
        $this->validate(['template' => [Rule::enum(TicketStatus::class)]]);
        $this->fillMessage();
    }

    /** Durum değişti / teklif gönderildi: güncel duruma uygun mesajı öner */
    #[On('suggest-notify')]
    public function suggestForCurrent(): void
    {
        $this->template = $this->ticket()->status->value;
        $this->fillMessage();
        $this->suggest = true;
    }

    public function dismiss(): void
    {
        $this->suggest = false;
    }

    /** Kanal açıldığında geçmişe yazılır (mesaj içeriğiyle). */
    public function logSent(string $channel): void
    {
        $labels = ['whatsapp' => 'WhatsApp', 'sms' => 'SMS', 'email' => 'E-posta', 'copy' => 'Panoya kopyalandı'];
        abort_unless(isset($labels[$channel]), 422);

        $this->ticket()->events()->create([
            'user_id' => Auth::id(),
            'type' => TicketEvent::TYPE_NOTIFIED,
            'note' => $labels[$channel].' ile bildirildi:'."\n".mb_substr($this->message, 0, 1500),
            'is_public' => false,
        ]);
        $this->suggest = false;
        $this->dispatch('ticket-updated');
    }

    public function render()
    {
        $ticket = $this->ticket();
        $client = $ticket->clientProduct->client;

        return view('livewire.tickets.ticket-notify', [
            'ticket' => $ticket,
            'client' => $client,
            'wa' => NotificationTemplates::waNumber($client->phone),
            'lastNotified' => $ticket->events()->where('type', TicketEvent::TYPE_NOTIFIED)->first(),
        ]);
    }
}
