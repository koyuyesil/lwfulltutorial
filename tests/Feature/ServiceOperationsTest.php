<?php

use App\Enums\TicketStatus;
use App\Livewire\Parts\PartsIndex;
use App\Livewire\Settings\SettingsIndex;
use App\Livewire\Tickets\TicketIntake;
use App\Livewire\Tickets\TicketNotify;
use App\Livewire\Tickets\TicketQuote;
use App\Livewire\Tickets\TicketsBoard;
use App\Livewire\Tickets\TicketsShow;
use App\Livewire\Tracking\SurveyPage;
use App\Livewire\Tracking\TrackRepair;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketEvent;
use App\Models\User;
use App\Support\NotificationTemplates;
use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Livewire\Volt;

beforeEach(fn () => Settings::flush());

/** Aynı kullanıcıya ait müşteri + cihaz + servis kaydı */
function serviceTicket(User $user, TicketStatus $status = TicketStatus::DIAGNOSING, array $device = []): Ticket
{
    $client = Client::factory()->for($user)->create(['phone' => '0532 111 22 33', 'first_name' => 'Ayşe']);
    $product = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13 Pro']);
    $cp = ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'product_id' => $product->id] + $device);

    return Ticket::factory()->for($user)->status($status)->create(['client_product_id' => $cp->id]);
}

/* ---------------------------------------------------------------- Parça ve stok */

test('parts can be created and stock is deducted by ticket items and restored on removal', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(PartsIndex::class)
        ->call('create')
        ->set('form.name', 'Redmi Note 13 Pro ekran')
        ->set('form.cost', '1200,50')
        ->set('form.price', '2000')
        ->set('form.stock', '3')
        ->set('form.min_stock', '1')
        ->call('save')
        ->assertHasNoErrors();

    $part = $user->parts()->first();
    expect((float) $part->cost)->toBe(1200.5)->and($part->stock)->toBe(3);

    $ticket = serviceTicket($user);
    $quote = Livewire::actingAs($user)->test(TicketQuote::class, ['ticketId' => $ticket->id])->call('addPart', $part->id)->call('addPart', $part->id);
    expect($part->fresh()->stock)->toBe(1)->and($ticket->items()->first()->qty)->toBe(2);

    $quote->call('remove', $ticket->items()->first()->id);
    expect($part->fresh()->stock)->toBe(3)->and($part->fresh()->isLow())->toBeFalse();

    // Başka kullanıcının parçası eklenemez
    $other = User::factory()->create();
    Livewire::actingAs($other)->test(PartsIndex::class)->call('edit', $part->id)->assertNotFound();
});

/* ---------------------------------------------------------------- Teklif ve online onay */

test('sending a quote moves the ticket to awaiting approval and the customer approves it online', function () {
    $user = User::factory()->create();
    $ticket = serviceTicket($user);

    Livewire::actingAs($user)->test(TicketQuote::class, ['ticketId' => $ticket->id])
        ->set('laborName', 'Ekran değişimi işçiliği')->set('laborPrice', '750')->call('addLabor')
        ->call('send')
        ->assertDispatched('suggest-notify');

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::AWAITING_APPROVAL)
        ->and($ticket->quote_status)->toBe('pending')
        ->and((float) $ticket->quote_total)->toBe(750.0);

    auth()->logout();
    Livewire::test(TrackRepair::class)
        ->set('code', $ticket->tracking_code)->set('phoneLast4', '2233')->call('track')
        ->assertSee('Teklifi onayla')
        ->set('quoteNote', 'Orijinal ekran olsun lütfen')
        ->call('decideQuote', true)
        ->assertSee('Onaylandı');

    $ticket->refresh();
    expect($ticket->quote_status)->toBe('approved')
        ->and($ticket->quote_decided_by)->toBe('customer')
        ->and($ticket->quote_decision_ip)->not->toBeNull()
        ->and($ticket->quote_customer_note)->toBe('Orijinal ekran olsun lütfen')
        ->and($ticket->status)->toBe(TicketStatus::IN_REPAIR);

    // İkinci karar reddedilir
    expect(fn () => $ticket->decideQuote(false))->toThrow(InvalidArgumentException::class);
});

test('the customer cannot decide a quote without verifying the tracking code', function () {
    $user = User::factory()->create();
    $ticket = serviceTicket($user);
    $ticket->items()->create(['kind' => 'labor', 'name' => 'İşçilik', 'qty' => 1, 'unit_price' => 100]);
    $ticket->sendQuote();

    Livewire::test(TrackRepair::class)->call('decideQuote', true)->assertHasErrors('quote');
    expect($ticket->fresh()->quote_status)->toBe('pending');
});

/* ---------------------------------------------------------------- Kabul formu */

test('intake details are saved, the lock secret is encrypted and photos are private', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $ticket = serviceTicket($user);

    Livewire::actingAs($user)->test(TicketIntake::class, ['ticketId' => $ticket->id])
        ->set('accessories', ['Şarj aleti', 'Kılıf'])
        ->set('conditions', ['Ekran çizik'])
        ->set('lockType', 'pin')->set('lockSecret', '12ab')->call('save')->assertHasErrors('lockSecret')
        ->set('lockSecret', '4321')->call('save')->assertHasNoErrors()
        ->set('uploads', [UploadedFile::fake()->image('on.jpg', 800, 600)]);

    $ticket->refresh();
    expect($ticket->intake['accessories'])->toBe(['Şarj aleti', 'Kılıf'])
        ->and($ticket->lock_secret)->toBe('4321')
        ->and(DB::table('tickets')->where('id', $ticket->id)->value('lock_secret'))->not->toContain('4321')
        ->and($ticket->photos)->toHaveCount(1);

    $photo = $ticket->photos->first();
    Storage::disk('local')->assertExists($photo->path);
    $this->actingAs($user)->get($photo->url())->assertOk();
    $this->actingAs(User::factory()->create())->get($photo->url())->assertNotFound();
});

test('the customer signature is stored only as a png data url', function () {
    $user = User::factory()->create();
    $ticket = serviceTicket($user);
    $png = 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('s.png', 10, 10)->getContent());

    Livewire::actingAs($user)->test(TicketIntake::class, ['ticketId' => $ticket->id])
        ->call('saveSignature', 'javascript:alert(1)')->assertHasErrors('signature')
        ->call('saveSignature', $png)->assertHasNoErrors();

    expect($ticket->fresh()->signed_at)->not->toBeNull()
        ->and($ticket->events()->where('type', TicketEvent::TYPE_INTAKE)->exists())->toBeTrue();
});

/* ---------------------------------------------------------------- Bildirimler */

test('notification templates are filled with ticket data and sending is logged', function () {
    $user = User::factory()->create();
    Settings::set($user, 'company.name', 'Koyu Servis');
    $ticket = serviceTicket($user, TicketStatus::READY);

    $text = NotificationTemplates::for($ticket);
    expect($text)->toContain('Ayşe')->toContain($ticket->tracking_code)->toContain('Koyu Servis')->toContain('Redmi Note 13 Pro')
        ->and(NotificationTemplates::waNumber('0532 111 22 33'))->toBe('905321112233')
        ->and(NotificationTemplates::waNumber('5321112233'))->toBe('905321112233');

    Livewire::actingAs($user)->test(TicketNotify::class, ['ticketId' => $ticket->id])
        ->assertSet('template', 'ready')
        ->assertSee('wa.me/905321112233', false)
        ->call('logSent', 'whatsapp');

    expect($ticket->events()->where('type', TicketEvent::TYPE_NOTIFIED)->first()->note)->toContain('WhatsApp');

    // Firma şablonu düzenleyince o kullanılır
    Settings::set($user, 'notify.templates', ['ready' => 'Hazır {kod}']);
    Settings::flush();
    expect(NotificationTemplates::for($ticket->fresh()))->toBe('Hazır '.$ticket->tracking_code);
});

test('settings manage technicians and only store customised templates', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(SettingsIndex::class, ['tab' => 'servis'])
        ->set('newTechnician', 'Mehmet Usta')->call('addTechnician')
        ->set('newTechnician', 'Mehmet Usta')->call('addTechnician')->assertHasErrors('newTechnician');
    expect($user->fresh()->technicians())->toBe(['Mehmet Usta']);

    Livewire::actingAs($user)->test(SettingsIndex::class, ['tab' => 'bildirimler'])
        ->set('templates.ready', 'Cihazınız hazır {ad}')
        ->call('saveTemplates');
    Settings::flush();
    expect($user->fresh()->setting('notify.templates'))->toBe(['ready' => 'Cihazınız hazır {ad}']);

    $this->actingAs($user)->get(route('settings', 'bildirimler'))->assertOk()->assertSee('Müşteri bildirim şablonları');
});

/* ---------------------------------------------------------------- İş panosu ve teknisyen */

test('the board moves cards only along allowed transitions and assigns technicians', function () {
    $user = User::factory()->create();
    Settings::set($user, 'tickets.technicians', ['Ali', 'Veli']);
    $ticket = serviceTicket($user, TicketStatus::RECEIVED);

    $board = Livewire::actingAs($user)->test(TicketsBoard::class)->assertSee($ticket->tracking_code);
    $board->call('move', $ticket->id, 'ready');
    expect($ticket->fresh()->status)->toBe(TicketStatus::RECEIVED);
    $board->call('move', $ticket->id, 'diagnosing');
    expect($ticket->fresh()->status)->toBe(TicketStatus::DIAGNOSING);

    $board->call('assign', $ticket->id, 'Veli');
    expect($ticket->fresh()->technician)->toBe('Veli');
    $board->set('technician', 'Ali')->assertDontSee($ticket->tracking_code);
    $board->call('assign', $ticket->id, 'Bilinmeyen')->assertStatus(422);

    Livewire::actingAs($user)->test(TicketsShow::class, ['ticket' => $ticket])->call('assignTechnician', 'Ali');
    expect($ticket->fresh()->technician)->toBe('Ali');
    $this->actingAs($user)->get(route('tickets.board'))->assertOk();
});

/* ---------------------------------------------------------------- Baskı, etiket, geçmiş */

test('print views render every format for the owner only', function () {
    $user = User::factory()->create();
    $ticket = serviceTicket($user);

    foreach (['a4', 'a4-2', 'a5', '80', '58', 'label'] as $format) {
        $this->actingAs($user)->get(route('tickets.print', [$ticket, $format]))
            ->assertOk()->assertSee($ticket->tracking_code)->assertSee('data-qr', false);
    }
    $this->actingAs($user)->get(route('tickets.print', [$ticket, 'a4']))->assertSee($ticket->trackingUrl(), false);
    $this->actingAs($user)->get(route('tickets.print', [$ticket, 'label']))->assertSee(route('tickets.show', $ticket), false);
    $this->actingAs(User::factory()->create())->get(route('tickets.print', [$ticket, 'a4']))->assertNotFound();
});

test('device history finds earlier tickets with the same imei', function () {
    $user = User::factory()->create();
    $old = serviceTicket($user, TicketStatus::DELIVERED, ['imei' => '356789012345678']);
    $new = serviceTicket($user, TicketStatus::RECEIVED, ['imei' => '356789012345678']);
    serviceTicket($user, TicketStatus::RECEIVED, ['imei' => '111111111111111']);

    expect($new->deviceHistory()->pluck('id')->all())->toBe([$old->id]);
    Livewire::actingAs($user)->test(TicketsShow::class, ['ticket' => $new])->assertSee($old->tracking_code)->assertSee('farklı müşteri');
});

/* ---------------------------------------------------------------- Memnuniyet anketi */

test('the survey link is signed and accepts a single answer', function () {
    $user = User::factory()->create();
    $ticket = serviceTicket($user, TicketStatus::DELIVERED);

    $this->get(route('survey', $ticket->tracking_code))->assertForbidden();
    $this->get($ticket->surveyUrl())->assertOk()->assertSee('Deneyiminizi puanlayın');

    Livewire::test(SurveyPage::class, ['ticket' => $ticket->tracking_code])
        ->call('submit')->assertHasErrors('score')
        ->set('score', 9)->set('comment', 'Çok hızlıydı')->call('submit')
        ->assertSee('Teşekkür ederiz');

    Livewire::test(SurveyPage::class, ['ticket' => $ticket->tracking_code])->set('score', 1)->call('submit');
    expect($ticket->fresh()->survey_score)->toBe(9)->and($ticket->fresh()->survey_comment)->toBe('Çok hızlıydı');

    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('+100');
});

/* ---------------------------------------------------------------- İşletme bilgileri ve profil */

test('business details are edited on the profile and used on prints and tracking', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile'))->assertOk()
        ->assertSee('İşletme bilgileri')->assertSee('Hesap bilgileri')->assertSeeVolt('profile.business-info-form');

    Volt\Volt::actingAs($user)->test('profile.business-info-form')
        ->set('company.name', 'Hızlı Tamir')
        ->set('company.legal_name', 'Hızlı Tamir Teknoloji Ltd. Şti.')
        ->set('company.phone', '0212 555 00 00')
        ->set('company.city', 'Kadıköy / İstanbul')
        ->set('company.tax_office', 'Kadıköy')
        ->set('company.tax_number', '12345')->call('save')->assertHasErrors('company.tax_number')
        ->set('company.tax_number', '1234567890')
        ->set('company.footer', 'Garanti 6 aydır')
        ->call('save')->assertHasNoErrors()
        ->set('logoUpload', UploadedFile::fake()->image('logo.png', 600, 300))
        ->assertHasNoErrors();

    Settings::flush();
    $company = \App\Support\Company::for($user->fresh());
    expect($company['legal_name'])->toBe('Hızlı Tamir Teknoloji Ltd. Şti.')
        ->and(\App\Support\Company::taxLine($company))->toBe('Kadıköy VD · 1234567890')
        ->and($company['logo'])->toStartWith('data:image/')
        ->and($user->fresh()->companyName())->toBe('Hızlı Tamir');

    $ticket = serviceTicket($user->fresh());
    $this->actingAs($user)->get(route('tickets.print', [$ticket, 'a4-2']))->assertOk()
        ->assertSee('MÜŞTERİ NÜSHASI')->assertSee('İŞLETME NÜSHASI')
        ->assertSee('Hızlı Tamir Teknoloji Ltd. Şti.')->assertSee('Kadıköy VD · 1234567890')->assertSee('Garanti 6 aydır');
    $this->actingAs($user)->get(route('tickets.print', [$ticket, 'a5']))->assertOk()->assertSee('A5 portrait', false);

    // Ayarlar > Genel aynı formu gösterir
    $this->actingAs($user)->get(route('settings', 'genel'))->assertOk()->assertSeeVolt('profile.business-info-form');
});