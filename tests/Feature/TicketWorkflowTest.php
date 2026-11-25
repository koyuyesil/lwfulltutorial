<?php

use App\Enums\PriorityType;
use App\Enums\TicketStatus;
use App\Livewire\Tickets\TicketsList;
use App\Livewire\Tickets\TicketsShow;
use App\Models\Ticket;
use App\Models\TicketEvent;
use App\Models\User;
use Livewire\Livewire;

test('new tickets start as received with an sla due date and a created event', function () {
    $this->freezeSecond(); // DB tarihleri saniye hassasiyetinde saklanır
    $ticket = Ticket::factory()->create(['status' => null, 'priority' => PriorityType::HIGH]);

    expect($ticket->status)->toBe(TicketStatus::RECEIVED)
        ->and($ticket->due_at->equalTo(now()->addHours(24)))->toBeTrue()
        ->and($ticket->events()->first()->type)->toBe(TicketEvent::TYPE_CREATED);
});

test('allowed transitions are applied and logged', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user)->status(TicketStatus::RECEIVED)->create();

    Livewire::actingAs($user)
        ->test(TicketsList::class)
        ->call('changeStatus', $ticket->id, 'diagnosing')
        ->assertHasNoErrors();

    $event = $ticket->events()->where('type', TicketEvent::TYPE_STATUS_CHANGED)->first();

    expect($ticket->fresh()->status)->toBe(TicketStatus::DIAGNOSING)
        ->and($event->from_status)->toBe(TicketStatus::RECEIVED)
        ->and($event->to_status)->toBe(TicketStatus::DIAGNOSING)
        ->and($event->user_id)->toBe($user->id);
});

test('skipping workflow steps is rejected', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user)->status(TicketStatus::RECEIVED)->create();

    Livewire::actingAs($user)
        ->test(TicketsList::class)
        ->call('changeStatus', $ticket->id, 'delivered')
        ->assertHasErrors('status.'.$ticket->id);

    expect($ticket->fresh()->status)->toBe(TicketStatus::RECEIVED);
});

test('closing a ticket records closed_at and sla result', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user)->status(TicketStatus::READY)->create();

    $this->actingAs($user);
    $ticket->transitionTo(TicketStatus::DELIVERED);

    expect($ticket->fresh()->closed_at)->not->toBeNull()
        ->and($ticket->fresh()->metSla())->toBeTrue()
        ->and($ticket->fresh()->slaState())->toBe('closed');
});

test('sla state reflects overdue and at risk tickets', function () {
    $user = User::factory()->create();
    $overdue = Ticket::factory()->for($user)->status(TicketStatus::IN_REPAIR)->create(['due_at' => now()->subHour()]);
    $risky = Ticket::factory()->for($user)->status(TicketStatus::IN_REPAIR)
        ->create(['priority' => PriorityType::NORMAL, 'due_at' => now()->addHours(5)]);
    $fine = Ticket::factory()->for($user)->status(TicketStatus::IN_REPAIR)
        ->create(['priority' => PriorityType::NORMAL, 'due_at' => now()->addHours(60)]);

    expect($overdue->slaState())->toBe('overdue')
        ->and($risky->slaState())->toBe('warning')
        ->and($fine->slaState())->toBe('ok')
        ->and(Ticket::overdue()->pluck('id')->all())->toBe([$overdue->id])
        ->and(Ticket::atRisk()->pluck('id')->all())->toBe([$risky->id]);

    Livewire::actingAs($user)->test(TicketsList::class)
        ->set('slaFilter', 'overdue')
        ->assertViewHas('tickets', fn ($t) => $t->pluck('id')->all() === [$overdue->id]);
});

test('detail page shows timeline and accepts notes and transitions', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user)->status(TicketStatus::DIAGNOSING)->create();

    $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk()->assertSee('Zaman çizelgesi');

    Livewire::actingAs($user)->test(TicketsShow::class, ['ticket' => $ticket])
        ->set('note', 'Anakart sıvı teması tespit edildi')
        ->set('notePublic', true)
        ->call('addNote')
        ->assertHasNoErrors()
        ->set('note', 'Müşteriye fiyat bildirildi')
        ->call('moveTo', 'awaiting_approval')
        ->assertSee('Anakart sıvı teması tespit edildi');

    expect($ticket->fresh()->status)->toBe(TicketStatus::AWAITING_APPROVAL)
        ->and($ticket->events()->where('type', 'note')->first()->is_public)->toBeTrue()
        ->and($ticket->events()->where('type', 'status_changed')->first()->note)->toBe('Müşteriye fiyat bildirildi');
});

test('detail page is not accessible for other users tickets', function () {
    $ticket = Ticket::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('tickets.show', $ticket))
        ->assertNotFound();
});
