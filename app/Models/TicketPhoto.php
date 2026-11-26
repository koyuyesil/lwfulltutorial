<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** Kabul fotoğrafı. Dosyalar özel diskte tutulur, yalnızca kayıt sahibine sunulur. */
class TicketPhoto extends Model
{
    protected $fillable = ['ticket_id', 'path', 'caption'];

    protected static function booted(): void
    {
        static::deleted(fn (TicketPhoto $photo) => Storage::disk('local')->delete($photo->path));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function url(): string
    {
        return route('tickets.photo', [$this->ticket_id, $this->id]);
    }

    /** Baskı için gömülü veri adresi */
    public function dataUri(): ?string
    {
        $disk = Storage::disk('local');
        if (! $disk->exists($this->path)) {
            return null;
        }

        return 'data:'.($disk->mimeType($this->path) ?: 'image/jpeg').';base64,'.base64_encode($disk->get($this->path));
    }
}
