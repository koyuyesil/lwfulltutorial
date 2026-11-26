<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Dijital kabul formu: aksesuarlar, dış durum (hasar kaydı), ekran kilidi, fotoğraflar ve müşteri imzası.
 * Fotoğraflar tarayıcıda küçültülüp yüklenir (telefon fotoğrafları sunucu sınırını aşmasın).
 */
class TicketIntake extends Component
{
    use WithFileUploads;

    public const MAX_PHOTOS = 12;

    #[Locked]
    public int $ticketId;

    public array $accessories = [];

    public array $conditions = [];

    public string $notes = '';

    public string $lockType = 'none';

    public string $lockSecret = '';

    /** @var array<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    public bool $revealLock = false;

    public function mount(int $ticketId): void
    {
        $this->ticketId = $ticketId;
        $ticket = $this->ticket();
        $intake = (array) $ticket->intake;
        $this->accessories = array_values((array) ($intake['accessories'] ?? []));
        $this->conditions = array_values((array) ($intake['conditions'] ?? []));
        $this->notes = (string) ($intake['notes'] ?? '');
        $this->lockType = $ticket->lock_type ?: 'none';
        $this->lockSecret = (string) $ticket->lock_secret;
    }

    protected function ticket(): Ticket
    {
        return Auth::user()->tickets()->with('photos')->findOrFail($this->ticketId);
    }

    public function save(): void
    {
        $this->validate([
            'accessories' => ['array'], 'accessories.*' => ['string', 'max:40'],
            'conditions' => ['array'], 'conditions.*' => ['string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'lockType' => ['required', Rule::in(['none', 'pin', 'pattern', 'password'])],
            'lockSecret' => ['nullable', 'string', 'max:64', Rule::when($this->lockType === 'pin', ['regex:/^\d{4,12}$/'])],
        ], ['lockSecret.regex' => 'PIN 4–12 rakam olmalıdır.']);

        $this->ticket()->update([
            'intake' => [
                'accessories' => array_values(array_unique($this->accessories)),
                'conditions' => array_values(array_unique($this->conditions)),
                'notes' => trim($this->notes) ?: null,
            ],
            'lock_type' => $this->lockType,
            'lock_secret' => $this->lockType === 'none' ? null : (trim($this->lockSecret) ?: null),
        ]);

        $this->dispatch('toast', message: 'Kabul formu kaydedildi.');
        $this->dispatch('ticket-updated');
    }

    /** Tarayıcıda küçültülmüş fotoğraflar yüklendikten sonra kaydedilir. */
    public function updatedUploads(): void
    {
        $ticket = $this->ticket();
        $room = self::MAX_PHOTOS - $ticket->photos->count();

        $this->validate(['uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']], [
            'uploads.*.image' => 'Yalnızca fotoğraf yüklenebilir.',
            'uploads.*.max' => 'Fotoğraf 4 MB\'tan küçük olmalı.',
        ]);

        $saved = 0;
        foreach (array_slice($this->uploads, 0, max(0, $room)) as $file) {
            $path = $file->storeAs('tickets/'.$ticket->id, Str::uuid().'.'.($file->guessExtension() ?: 'jpg'), 'local');
            $ticket->photos()->create(['path' => $path]);
            $saved++;
        }
        $this->uploads = [];

        if ($saved) {
            $ticket->events()->create(['user_id' => Auth::id(), 'type' => TicketEvent::TYPE_INTAKE, 'note' => "{$saved} kabul fotoğrafı eklendi.", 'is_public' => false]);
            $this->dispatch('toast', message: "{$saved} fotoğraf eklendi.");
        }
        if ($room <= 0) {
            $this->addError('uploads', 'En fazla '.self::MAX_PHOTOS.' fotoğraf eklenebilir.');
        }
    }

    public function caption(int $photoId, string $caption): void
    {
        $photo = $this->ticket()->photos->firstWhere('id', $photoId) ?? abort(404);
        $photo->update(['caption' => mb_substr(trim($caption), 0, 120) ?: null]);
    }

    public function deletePhoto(int $photoId): void
    {
        ($this->ticket()->photos->firstWhere('id', $photoId) ?? abort(404))->delete();
    }

    public function saveSignature(string $dataUrl): void
    {
        if (! preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $dataUrl) || strlen($dataUrl) > 500_000) {
            $this->addError('signature', 'İmza okunamadı; tekrar deneyin.');

            return;
        }

        $ticket = $this->ticket();
        $ticket->update(['signature' => $dataUrl, 'signed_at' => now()]);
        $ticket->events()->create(['user_id' => Auth::id(), 'type' => TicketEvent::TYPE_INTAKE, 'note' => 'Müşteri kabul formunu imzaladı.', 'is_public' => false]);
        $this->dispatch('toast', message: 'İmza kaydedildi.');
        $this->dispatch('ticket-updated');
    }

    public function clearSignature(): void
    {
        $this->ticket()->update(['signature' => null, 'signed_at' => null]);
    }

    public function render()
    {
        return view('livewire.tickets.ticket-intake', ['ticket' => $this->ticket()]);
    }
}
