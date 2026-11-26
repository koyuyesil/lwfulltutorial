<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Servis kaydı belgeleri: kabul fotoğrafları ve baskılar (A4 fiş, termal fiş, QR etiket). */
class TicketDocumentsController extends Controller
{
    public const FORMATS = ['a4', 'a4-2', 'a5', '80', '58', 'label'];

    protected function owned(Request $request, Ticket $ticket): Ticket
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        return $ticket;
    }

    public function photo(Request $request, Ticket $ticket, TicketPhoto $photo): StreamedResponse
    {
        $this->owned($request, $ticket);
        abort_unless($photo->ticket_id === $ticket->id && Storage::disk('local')->exists($photo->path), 404);

        return Storage::disk('local')->response($photo->path, null, ['Cache-Control' => 'private, max-age=86400']);
    }

    public function print(Request $request, Ticket $ticket, string $format)
    {
        $ticket = $this->owned($request, $ticket)->load([
            'clientProduct.client', 'clientProduct.product', 'items', 'photos', 'user',
        ]);

        return view('tickets.print', [
            'ticket' => $ticket,
            'format' => $format,
            'company' => \App\Support\Company::for($ticket->user),
            'autoprint' => ! $request->boolean('onizleme'),
        ]);
    }
}
