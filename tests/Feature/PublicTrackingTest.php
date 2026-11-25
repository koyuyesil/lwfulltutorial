<?php

use App\Enums\TicketStatus;
use App\Livewire\Tracking\TrackRepair;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Ticket;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

function trackableTicket(): Ticket
{
    $client = Client::factory()->create(['phone' => '0 (532) 111 22 33']);
    $device = ClientProduct::factory()->create(['client_id' => $client->id, 'user_id' => $client->user_id]);

    return Ticket::factory()->status(TicketStatus::IN_REPAIR)->create([
        'user_id' => $client->user_id,
        'client_product_id' => $device->id,
    ]);
}

beforeEach(fn () => RateLimiter::clear('track:127.0.0.1'));

test('tickets get a unique tracking code', function () {
    $a = Ticket::factory()->create();
    $b = Ticket::factory()->create();

    expect($a->tracking_code)->toMatch('/^KM-[2-9A-HJKMNP-Z]{8}$/')
        ->and($a->tracking_code)->not->toBe($b->tracking_code);
});

test('customer can track a repair with code and last 4 phone digits', function () {
    $ticket = trackableTicket();
    $ticket->addNote('Ekran siparişi verildi', public: true);
    $ticket->addNote('Müşteri pazarlıkçı', public: false);

    Livewire::test(TrackRepair::class)
        ->set('code', strtolower(str_replace('-', ' ', $ticket->tracking_code)))
        ->set('phoneLast4', '2233')
        ->call('track')
        ->assertHasNoErrors()
        ->assertSee('Cihazınız onarılıyor')
        ->assertSee('Ekran siparişi verildi')
        ->assertDontSee('Müşteri pazarlıkçı');
});

test('wrong phone digits do not reveal the ticket', function () {
    $ticket = trackableTicket();

    Livewire::test(TrackRepair::class)
        ->set('code', $ticket->tracking_code)
        ->set('phoneLast4', '9999')
        ->call('track')
        ->assertHasErrors('code')
        ->assertSet('ticketId', null)
        ->assertDontSee('Cihazınız onarılıyor');
});

test('tracking is rate limited', function () {
    $ticket = trackableTicket();
    $component = Livewire::test(TrackRepair::class)->set('code', $ticket->tracking_code);

    foreach (range(1, TrackRepair::MAX_ATTEMPTS) as $_) {
        $component->set('phoneLast4', '0000')->call('track');
    }

    $component->set('phoneLast4', '2233')->call('track')
        ->assertSet('ticketId', null)
        ->assertSee('Çok fazla deneme');
});

test('tracking page and landing page are public', function () {
    $this->get(route('tracking'))->assertOk()->assertSee('Arıza Takibi');
    $this->get('/')->assertOk()->assertSee(route('tracking'), false);
});

test('board ids live under tools', function () {
    expect(route('board-ids.index', absolute: false))->toBe('/tools/board-ids');
});
