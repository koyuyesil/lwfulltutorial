<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketItem;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Servis kaydının parça / işçilik kalemleri, teklif gönderme ve onay. */
class TicketQuote extends Component
{
    #[Locked]
    public int $ticketId;

    public string $partSearch = '';

    public string $laborName = '';

    public string $laborPrice = '';

    protected function ticket(): Ticket
    {
        return Auth::user()->tickets()->with('items.part')->findOrFail($this->ticketId);
    }

    #[Computed]
    public function partResults()
    {
        $term = trim($this->partSearch);
        $ticket = Auth::user()->tickets()->with('clientProduct.product')->find($this->ticketId);
        $product = $ticket?->clientProduct?->product;

        // Çapraz referans: cihazdaki parçalar ve onların muadilleri
        $partIds = collect();
        if ($product) {
            foreach ($product->parts()->with(['compatibleTo', 'compatibleFrom'])->get() as $onDevice) {
                $partIds = $partIds->push($onDevice->id)->merge($onDevice->compatibles()->pluck('id'));
            }
        }

        return Auth::user()->parts()
            ->when(mb_strlen($term) >= 2, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%$term%")->orWhere('sku', 'like', "%$term%")->orWhere('compatible', 'like', "%$term%")))
            // Arama yoksa bu cihaza uyumlu parçalar önerilir
            ->when(mb_strlen($term) < 2, fn ($q) => $q->where(fn ($q) => $q
                ->when($product?->codename, fn ($q) => $q->orWhere('compatible', 'like', '%'.$product->codename.'%'))
                ->when($product?->model_name, fn ($q) => $q->orWhere('compatible', 'like', '%'.$product->model_name.'%')->orWhere('name', 'like', '%'.$product->model_name.'%'))
                ->when($partIds->isNotEmpty(), fn ($q) => $q->orWhereIn('id', $partIds->unique()->all()))
                ->orWhereRaw('1 = 0')))
            ->orderBy('name')->limit(6)->get();
    }

    public function addPart(int $partId): void
    {
        $part = Auth::user()->parts()->findOrFail($partId);
        $ticket = $this->ticket();

        $existing = $ticket->items->firstWhere('part_id', $part->id);
        if ($existing) {
            $existing->update(['qty' => $existing->qty + 1]);
        } else {
            $ticket->items()->create([
                'part_id' => $part->id, 'kind' => TicketItem::KIND_PART, 'name' => $part->name,
                'qty' => 1, 'unit_cost' => $part->cost, 'unit_price' => $part->price,
            ]);
        }

        $this->partSearch = '';
        unset($this->partResults);
        if ($part->fresh()->stock < 0) {
            $this->dispatch('toast', message: "{$part->name} stokta yok; stok eksiye düştü. Parça siparişi gerekebilir.", type: 'error');
        }
        $this->changed();
    }

    public function addLabor(): void
    {
        $this->laborPrice = str_replace(',', '.', $this->laborPrice);
        $this->validate([
            'laborName' => ['required', 'string', 'min:2', 'max:120'],
            'laborPrice' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ], [
            'laborName.required' => 'İşçilik açıklaması yazın.',
            'laborPrice.required' => 'Tutar girin.',
        ]);

        $this->ticket()->items()->create([
            'kind' => TicketItem::KIND_LABOR, 'name' => trim($this->laborName), 'qty' => 1,
            'unit_cost' => 0, 'unit_price' => (float) $this->laborPrice,
        ]);

        $this->reset('laborName', 'laborPrice');
        $this->changed();
    }

    public function setQty(int $itemId, $qty): void
    {
        $item = $this->ticket()->items->firstWhere('id', $itemId) ?? abort(404);
        $item->update(['qty' => max(1, min(999, (int) $qty))]);
        $this->changed();
    }

    public function setPrice(int $itemId, $price): void
    {
        $price = (float) str_replace(',', '.', (string) $price);
        $item = $this->ticket()->items->firstWhere('id', $itemId) ?? abort(404);
        $item->update(['unit_price' => max(0, min(9999999, $price))]);
        $this->changed();
    }

    public function remove(int $itemId): void
    {
        ($this->ticket()->items->firstWhere('id', $itemId) ?? abort(404))->delete();
        $this->changed();
    }

    public function send(): void
    {
        $ticket = $this->ticket();
        if ($ticket->items->isEmpty()) {
            $this->addError('quote', 'Teklif göndermek için en az bir kalem ekleyin.');

            return;
        }
        if ($ticket->status->isClosed()) {
            $this->addError('quote', 'Kapanmış kayda teklif gönderilemez.');

            return;
        }

        $ticket->sendQuote();
        $this->dispatch('ticket-updated');
        $this->dispatch('suggest-notify');
        $this->dispatch('toast', message: 'Teklif müşterinin takip sayfasına eklendi.');
    }

    /** Müşteri telefonla onay / ret verdiğinde servis adına karar */
    public function decide(bool $approved): void
    {
        try {
            $this->ticket()->decideQuote($approved, 'staff');
        } catch (InvalidArgumentException $e) {
            $this->addError('quote', $e->getMessage());

            return;
        }
        $this->dispatch('ticket-updated');
        $this->dispatch('toast', message: $approved ? 'Teklif onaylandı olarak işaretlendi.' : 'Teklif reddedildi olarak işaretlendi.');
    }

    protected function changed(): void
    {
        $this->resetValidation();
        $this->dispatch('ticket-updated');
    }

    public function render()
    {
        $ticket = $this->ticket();

        return view('livewire.tickets.ticket-quote', [
            'ticket' => $ticket,
            'total' => $ticket->itemsTotal(),
            'cost' => $ticket->itemsCost(),
            'outdated' => $ticket->quote_status && round((float) $ticket->quote_total, 2) !== round($ticket->itemsTotal(), 2),
        ]);
    }
}
