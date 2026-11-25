<?php

namespace App\Livewire\Tracking;

use App\Models\Ticket;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Arıza Takibi')]
class TrackRepair extends Component
{
    public const MAX_ATTEMPTS = 8;

    #[Url(as: 'kod', except: '')]
    public string $code = '';

    public string $phoneLast4 = '';

    #[Locked]
    public ?int $ticketId = null;

    public function track(): void
    {
        $this->ticketId = null;

        $this->validate([
            'code' => 'required|string|min:6|max:20',
            'phoneLast4' => 'required|digits:4',
        ], [
            'code.required' => 'Takip kodunu girin.',
            'phoneLast4.required' => 'Telefonunuzun son 4 hanesini girin.',
            'phoneLast4.digits' => 'Telefonun son 4 hanesi 4 rakam olmalıdır.',
        ]);

        $key = 'track:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->addError('code', 'Çok fazla deneme yapıldı. Lütfen '.RateLimiter::availableIn($key).' saniye sonra tekrar deneyin.');

            return;
        }

        $ticket = Ticket::with('clientProduct.client')
            ->where('tracking_code', Ticket::normalizeTrackingCode($this->code))
            ->first();

        $phoneDigits = preg_replace('/\D/', '', (string) $ticket?->clientProduct?->client?->phone);

        // Kod ya da telefon eşleşmezse aynı mesaj: hangi bilginin yanlış olduğu sızdırılmaz
        if (! $ticket || strlen($phoneDigits) < 4 || ! hash_equals(substr($phoneDigits, -4), $this->phoneLast4)) {
            RateLimiter::hit($key, 60);
            $this->addError('code', 'Bu bilgilerle eşleşen bir servis kaydı bulunamadı.');

            return;
        }

        RateLimiter::clear($key);
        $this->ticketId = $ticket->id;
        $this->code = $ticket->tracking_code;
    }

    public function resetSearch(): void
    {
        $this->reset('code', 'phoneLast4', 'ticketId');
    }

    public function render()
    {
        $ticket = $this->ticketId
            ? Ticket::with([
                'clientProduct.product',
                'user',
                'events' => fn ($q) => $q->where('is_public', true),
            ])->find($this->ticketId)
            : null;

        return view('livewire.tracking.track-repair', ['ticket' => $ticket]);
    }
}
