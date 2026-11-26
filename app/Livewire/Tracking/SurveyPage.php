<?php

namespace App\Livewire\Tracking;

use App\Models\Ticket;
use App\Models\TicketEvent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Teslim sonrası memnuniyet anketi (NPS 0–10). Bağlantı imzalı ve sürelidir; kayıt başına tek yanıt. */
#[Layout('layouts.guest')]
#[Title('Memnuniyet anketi')]
class SurveyPage extends Component
{
    #[Locked]
    public int $ticketId;

    public ?int $score = null;

    public string $comment = '';

    public function mount(string $ticket): void
    {
        $model = Ticket::where('tracking_code', $ticket)->firstOrFail();
        $this->ticketId = $model->id;
    }

    public function submit(): void
    {
        $ticket = Ticket::findOrFail($this->ticketId);
        if (! is_null($ticket->survey_score)) {
            return;
        }

        $this->validate([
            'score' => ['required', 'integer', 'between:0,10'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], ['score.required' => 'Lütfen 0 ile 10 arasında bir puan seçin.']);

        $ticket->forceFill([
            'survey_score' => $this->score,
            'survey_comment' => trim($this->comment) ?: null,
            'survey_at' => now(),
        ])->save();

        $ticket->events()->create([
            'type' => TicketEvent::TYPE_NOTE,
            'note' => "Memnuniyet anketi: {$this->score}/10".($this->comment ? "\n“".trim($this->comment).'”' : ''),
            'is_public' => false,
        ]);
    }

    public function render()
    {
        return view('livewire.tracking.survey-page', [
            'ticket' => Ticket::with('clientProduct.client', 'clientProduct.product', 'user')->findOrFail($this->ticketId),
        ]);
    }
}
