<?php

use App\Enums\TicketStatus;
use App\Livewire\Clients\ClientsForm;
use App\Livewire\Clients\ClientsList;
use App\Livewire\Products\ProductsForm;
use App\Livewire\Products\ProductsList;
use App\Livewire\Tickets\TicketsForm;
use App\Livewire\Tickets\TicketsList;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

/* ---------------------------------------------------------------- Müşteriler */

test('clients can be searched by name, phone digits and imei', function () {
    $user = User::factory()->create();
    $ayse = Client::factory()->for($user)->create(['first_name' => 'Ayşe', 'last_name' => 'Yılmaz', 'phone' => '0532 111 22 33']);
    $mehmet = Client::factory()->for($user)->create(['first_name' => 'Mehmet', 'last_name' => 'Kaya', 'phone' => '0555 999 88 77']);
    ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => $mehmet->id, 'imei' => '356789012345678']);

    $ids = fn ($search) => Livewire::actingAs($user)->test(ClientsList::class)->set('search', $search)
        ->viewData('clients')->pluck('id')->all();

    expect($ids('ayşe yıl'))->toBe([$ayse->id])
        ->and($ids('5321112233'))->toBe([$ayse->id])
        ->and($ids('356789'))->toBe([$mehmet->id]);
});

test('client form validates with turkish messages and normalizes names', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(ClientsForm::class)
        ->set('form.phone', '123')
        ->call('save')
        ->assertHasErrors(['form.first_name', 'form.last_name', 'form.phone']);

    Livewire::actingAs($user)->test(ClientsForm::class)
        ->set('form.first_name', 'ilker')
        ->set('form.last_name', 'IŞIK')
        ->set('form.phone', '0532 123 45 67')
        ->set('form.company', 'istanbul teknik')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('client-saved')
        ->assertDispatched('toast');

    $client = $user->clients()->first();
    expect($client->first_name)->toBe('İlker')
        ->and($client->last_name)->toBe('Işık')
        ->and($client->company)->toBe('İSTANBUL TEKNİK')
        ->and($client->email)->toBeNull();
});

test('client form warns about duplicate phone numbers', function () {
    $user = User::factory()->create();
    $existing = Client::factory()->for($user)->create(['phone' => '0532 123 45 67']);

    Livewire::actingAs($user)->test(ClientsForm::class)
        ->set('form.phone', '+90 532 123 45 67')
        ->assertSet('duplicate.id', $existing->id);
});

test('client card shows devices and service history, never another users client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $device = ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
    $ticket = Ticket::factory()->for($user)->create(['client_product_id' => $device->id, 'problem' => 'Hoparlör cızırtılı']);

    Livewire::actingAs($user)->test(\App\Livewire\Clients\ClientsDetail::class)
        ->call('load', $client->id)
        ->assertSee($client->full_name)
        ->assertSee($ticket->tracking_code)
        ->assertSee('Hoparlör cızırtılı');

    Livewire::actingAs($user)->test(\App\Livewire\Clients\ClientsDetail::class)
        ->call('load', Client::factory()->create()->id)
        ->assertNotFound();
});

test('another users client cannot be edited', function () {
    $foreign = Client::factory()->create();

    Livewire::actingAs(User::factory()->create())->test(ClientsForm::class)
        ->call('edit', $foreign->id)
        ->assertNotFound();
});

/* ---------------------------------------------------------------- Modeller */

test('product form defaults manufacturer to brand and allows optional fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(ProductsForm::class)
        ->set('form.brand', 'LG')
        ->set('form.model_name', 'G8')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('product-saved');

    $product = $user->products()->first();
    expect($product->manufacturer)->toBe('Lg')
        ->and($product->model_number)->toBeNull();
});

test('products can be filtered by brand', function () {
    $user = User::factory()->create();
    Product::factory()->for($user)->create(['brand' => 'Apple']);
    $samsung = Product::factory()->for($user)->create(['brand' => 'Samsung']);

    Livewire::actingAs($user)->test(ProductsList::class)
        ->set('brand', 'Samsung')
        ->assertViewHas('products', fn ($p) => $p->pluck('id')->all() === [$samsung->id]);
});

/* ---------------------------------------------------------------- Servis kaydı */

test('a ticket can be opened for a brand new client and model in one step', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(TicketsForm::class)
        ->call('setClientMode', 'new')
        ->set('newClient.first_name', 'Zeynep')
        ->set('newClient.last_name', 'Demir')
        ->set('newClient.phone', '0533 444 55 66')
        ->call('setProductMode', 'new')
        ->set('newProduct.brand', 'Xiaomi')
        ->set('newProduct.model_name', 'Redmi Note 12')
        ->set('imei', '351234567890123')
        ->call('addTag', 'Şarj olmuyor')
        ->call('create')
        ->assertHasNoErrors()
        ->assertSet('createdTicketId', fn ($id) => $id !== null)
        ->assertSee('Servis kaydı açıldı');

    $ticket = $user->tickets()->first();
    expect($ticket->problem)->toBe('Şarj olmuyor')
        ->and($ticket->clientProduct->client->full_name)->toBe('Zeynep Demir')
        ->and($ticket->clientProduct->product->display_name)->toBe('Xiaomi Redmi Note 12')
        ->and($ticket->clientProduct->imei)->toBe('351234567890123');
});

test('an existing device of the client can be reused', function () {
    $user = User::factory()->create();
    $device = ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => Client::factory()->for($user)]);

    Livewire::actingAs($user)->test(TicketsForm::class)
        ->call('selectClient', $device->client_id)
        ->call('selectDevice', $device->id)
        ->set('problem', 'Ekran titriyor')
        ->call('create')
        ->assertHasNoErrors();

    expect($user->tickets()->first()->client_product_id)->toBe($device->id)
        ->and(ClientProduct::count())->toBe(1);
});

test('returning device is detected by imei', function () {
    $user = User::factory()->create();
    $device = ClientProduct::factory()->create(['user_id' => $user->id, 'imei' => '359999999999999', 'client_id' => Client::factory()->for($user)]);
    Ticket::factory()->for($user)->create(['client_product_id' => $device->id]);

    Livewire::actingAs($user)->test(TicketsForm::class)
        ->set('imei', '359999999999999')
        ->assertSee('Bu cihaz daha önce kaydedilmiş')
        ->call('useKnownDevice')
        ->assertSet('selectedClientProduct', $device->id)
        ->assertSet('selectedClient', $device->client_id);
});

test('ticket form is prefilled from the musteri query parameter', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('tickets.index', ['yeni' => 1, 'musteri' => $client->id]))
        ->assertOk()
        ->assertSee($client->full_name);
});

test('tickets can be searched by tracking code and client phone, including closed ones', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create(['phone' => '0544 777 66 55']);
    $device = ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
    $closed = Ticket::factory()->for($user)->status(TicketStatus::DELIVERED)->create(['client_product_id' => $device->id]);
    Ticket::factory()->for($user)->status(TicketStatus::RECEIVED)->create();

    Livewire::actingAs($user)->test(TicketsList::class)
        ->set('search', strtolower($closed->tracking_code))
        ->assertViewHas('tickets', fn ($t) => $t->pluck('id')->all() === [$closed->id])
        ->set('search', '7776655')
        ->assertViewHas('tickets', fn ($t) => $t->pluck('id')->all() === [$closed->id]);
});
