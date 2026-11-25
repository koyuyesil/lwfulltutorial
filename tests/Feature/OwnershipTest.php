<?php

use App\Livewire\Clients\ClientsList;
use App\Livewire\Products\ProductsForm;
use App\Livewire\Search;
use App\Livewire\Tasks\TasksList;
use App\Livewire\Tickets\TicketsForm;
use App\Livewire\Tickets\TicketsList;
use App\Models\Client;
use App\Models\Product;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

test('a user cannot delete another users client', function () {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    Livewire::actingAs(User::factory()->create())
        ->test(ClientsList::class)
        ->call('delete', $client->id)
        ->assertNotFound();

    expect($client->fresh())->not->toBeNull();
});

test('a user can delete their own client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Livewire::actingAs($user)->test(ClientsList::class)->call('delete', $client->id);

    expect($client->fresh())->toBeNull();
});

test('a user cannot load another users product into the edit form', function () {
    $product = Product::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test(ProductsForm::class)
        ->call('edit', $product->id)
        ->assertNotFound();
});

test('a user cannot change status of another users ticket or task', function () {
    $ticket = Ticket::factory()->create(['status' => 'received']);
    $task = Task::factory()->create(['status' => 'open']);
    $intruder = User::factory()->create();

    Livewire::actingAs($intruder)->test(TicketsList::class)->call('changeStatus', $ticket->id, 'diagnosing')->assertNotFound();
    Livewire::actingAs($intruder)->test(TasksList::class)->call('changeStatus', $task->id, 'done')->assertNotFound();

    expect($ticket->fresh()->status->value)->toBe('received');
    expect($task->fresh()->status->value)->toBe('open');
});

test('search only returns the current users tasks', function () {
    $user = User::factory()->create();
    Task::factory()->for($user)->create(['title' => 'Ekran değişimi kendi']);
    Task::factory()->create(['title' => 'Başkası', 'description' => 'Ekran değişimi başkası']);

    Livewire::actingAs($user)
        ->test(Search::class)
        ->set('search', 'Ekran')
        ->assertViewHas('results', fn ($results) => $results->count() === 1
            && $results->first()->user_id === $user->id);
});

test('a ticket can be created for an existing client and product', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $product = Product::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(TicketsForm::class)
        ->set('selectedClient', $client->id)
        ->set('selectedProduct', $product->id)
        ->set('problem', 'Ekran kırık, dokunmatik çalışmıyor')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('ticket-created');

    expect($user->tickets()->count())->toBe(1);
    expect($user->tickets()->first()->clientProduct->client_id)->toBe($client->id);
});

test('a ticket cannot be created with another users client', function () {
    $user = User::factory()->create();
    $foreignClient = Client::factory()->create();
    $product = Product::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(TicketsForm::class)
        ->set('selectedProduct', $product->id)
        ->set('selectedClient', $foreignClient->id)
        ->set('problem', 'Şarj olmuyor')
        ->call('create')
        ->assertHasErrors('selectedClient');

    expect(Ticket::count())->toBe(0);
});

test('authenticated pages render', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(route($route))
        ->assertOk();
})->with(['dashboard', 'clients.index', 'products.index', 'tickets.index', 'tasks.index', 'board-ids.index', 'tools.index', 'profile']);

test('list components render with seeded data', function (string $component) {
    $this->seed();

    Livewire::actingAs(User::where('email', 'admin@example.com')->first())
        ->test($component)
        ->assertOk();
})->with([
    ClientsList::class,
    TicketsList::class,
    TasksList::class,
    \App\Livewire\Products\ProductsList::class,
    \App\Livewire\BoardIds\BoardIdsList::class,
    \App\Livewire\Dashboard\ClientsProductsList::class,
    \App\Livewire\Dashboard\DashboardIndex::class,
]);

test('public pages render with guest navigation', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertSee(route('register'), false)
        ->assertSee(route('services.index'), false);

    $this->get(route('login'))->assertOk()->assertSee(route('register'), false);
});
