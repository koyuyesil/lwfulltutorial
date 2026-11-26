<?php

use App\Livewire\Clients\ClientsList;
use App\Livewire\Parts\PartsIndex;
use App\Livewire\Settings\TrashBin;
use App\Livewire\Tasks\TasksList;
use App\Livewire\Tickets\TicketsList;
use App\Livewire\TrashUndo;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Part;
use App\Models\Product;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

/** Kullanıcıya ait müşteri + cihaz + servis kaydı */
function trashFixture(User $user): array
{
    $client = Client::factory()->for($user)->create();
    $product = Product::factory()->for($user)->create();
    $device = ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'product_id' => $product->id]);
    $ticket = Ticket::factory()->for($user)->create(['client_product_id' => $device->id]);

    return [$client, $product, $device, $ticket];
}

test('deleting a ticket moves it to the trash and shows an undo toast', function () {
    $user = User::factory()->create();
    [, , , $ticket] = trashFixture($user);

    Livewire::actingAs($user)->test(TicketsList::class)
        ->call('delete', $ticket->id)
        ->assertDispatched('toast', fn ($name, $p) => ($p['action']['event'] ?? null) === 'trash-restore'
            && $p['action']['params'] === ['type' => 'ticket', 'id' => $ticket->id]);

    expect(Ticket::find($ticket->id))->toBeNull()
        ->and(Ticket::withTrashed()->find($ticket->id)->trashed())->toBeTrue();
});

test('undo restores a trashed ticket', function () {
    $user = User::factory()->create();
    [, , , $ticket] = trashFixture($user);
    $ticket->delete();

    Livewire::actingAs($user)->test(TrashUndo::class)
        ->dispatch('trash-restore', type: 'ticket', id: $ticket->id)
        ->assertDispatched('trash-restored');

    expect(Ticket::find($ticket->id))->not->toBeNull();
});

test('a user cannot restore another users trashed record', function () {
    [, , , $ticket] = trashFixture(User::factory()->create());
    $ticket->delete();

    Livewire::actingAs(User::factory()->create())->test(TrashUndo::class)
        ->dispatch('trash-restore', type: 'ticket', id: $ticket->id)
        ->assertNotFound();

    expect(Ticket::find($ticket->id))->toBeNull();
});

test('deleting a client cascades to devices and tickets and undo brings them back together', function () {
    $user = User::factory()->create();
    [$client, , $device, $ticket] = trashFixture($user);

    // Daha önce ayrıca silinmiş servis kaydı, müşteri geri alınınca çöpte kalmalı
    $older = Ticket::factory()->for($user)->create(['client_product_id' => $device->id]);
    $older->delete();
    Ticket::withTrashed()->whereKey($older->id)->update(['deleted_at' => now()->subDay()]);

    Livewire::actingAs($user)->test(ClientsList::class)->call('delete', $client->id);

    expect(ClientProduct::find($device->id))->toBeNull()
        ->and(Ticket::find($ticket->id))->toBeNull()
        ->and($user->tickets()->count())->toBe(0);

    $client->fresh()->restore();

    expect(ClientProduct::find($device->id))->not->toBeNull()
        ->and(Ticket::find($ticket->id))->not->toBeNull()
        ->and(Ticket::find($older->id))->toBeNull();
});

test('deleting a device model cascades to client devices and tickets', function () {
    $user = User::factory()->create();
    [, $product, $device, $ticket] = trashFixture($user);

    $product->delete();
    expect(Ticket::find($ticket->id))->toBeNull();

    Product::withTrashed()->find($product->id)->restore();
    expect(ClientProduct::find($device->id))->not->toBeNull()
        ->and(Ticket::find($ticket->id))->not->toBeNull();
});

test('trash bin lists only root records and can restore or permanently delete', function () {
    $user = User::factory()->create();
    [$client, , , $ticket] = trashFixture($user);
    $part = Part::create(['user_id' => $user->id, 'name' => 'Ekran', 'stock' => 1]);

    $client->delete();   // servis kaydı müşteriyle birlikte gider ama listede ayrıca görünmez
    $part->delete();

    $bin = Livewire::actingAs($user)->test(TrashBin::class)
        ->assertSee($client->full_name)
        ->assertSee('Ekran')
        ->assertDontSee($ticket->tracking_code);

    $bin->call('restore', 'client', $client->id);
    expect(Client::find($client->id))->not->toBeNull()
        ->and(Ticket::find($ticket->id))->not->toBeNull();

    $bin->call('forceDelete', 'part', $part->id);
    expect(Part::withTrashed()->find($part->id))->toBeNull();
});

test('emptying the trash permanently deletes children through database cascade', function () {
    $user = User::factory()->create();
    [$client, , $device, $ticket] = trashFixture($user);
    $client->delete();

    Livewire::actingAs($user)->test(TrashBin::class)->call('emptyTrash');

    expect(Client::withTrashed()->find($client->id))->toBeNull()
        ->and(ClientProduct::withTrashed()->find($device->id))->toBeNull()
        ->and(Ticket::withTrashed()->find($ticket->id))->toBeNull();
});

test('model:prune removes records trashed longer than the retention period', function () {
    $user = User::factory()->create();
    $old = Part::create(['user_id' => $user->id, 'name' => 'Eski', 'stock' => 0]);
    $recent = Part::create(['user_id' => $user->id, 'name' => 'Yeni', 'stock' => 0]);
    $old->delete();
    $recent->delete();
    Part::withTrashed()->whereKey($old->id)->update(['deleted_at' => now()->subDays(31)]);

    Artisan::call('model:prune', ['--model' => [Part::class]]);

    expect(Part::withTrashed()->find($old->id))->toBeNull()
        ->and(Part::withTrashed()->find($recent->id))->not->toBeNull();
});

test('deleting a part and a task offers undo', function () {
    $user = User::factory()->create();
    $part = Part::create(['user_id' => $user->id, 'name' => 'Batarya', 'stock' => 2]);
    $task = Task::factory()->for($user)->create();

    Livewire::actingAs($user)->test(PartsIndex::class)->call('delete', $part->id)
        ->assertDispatched('toast', fn ($n, $p) => ($p['action']['params']['type'] ?? null) === 'part');
    Livewire::actingAs($user)->test(TasksList::class)->call('delete', $task->id)
        ->assertDispatched('toast', fn ($n, $p) => ($p['action']['params']['type'] ?? null) === 'task');

    expect(Part::find($part->id))->toBeNull()->and(Task::find($task->id))->toBeNull();
});
