<?php

use App\Livewire\Clients\ClientsDetail;
use App\Livewire\Settings\ClientImport;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientContacts;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

function contactsFile(string $content, string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $content);
}

function turkishClient(User $user): Client
{
    return Client::factory()->for($user)->create([
        'first_name' => 'Şükrü Çağlar',
        'last_name' => 'Öztürk',
        'phone' => '0532 123 45 67',
        'email' => 'sukru@example.com',
        'company' => 'Işık Elektronik; Ltd',
        'address' => 'Atatürk Cad. No:5, Kadıköy İstanbul',
    ]);
}

test('vcard is generated in every version and round-trips with turkish characters', function (string $version) {
    $user = User::factory()->create();
    $client = turkishClient($user);
    $contacts = app(ClientContacts::class);

    $card = $contacts->vcard($client, $version);

    expect($card)->toStartWith("BEGIN:VCARD\r\nVERSION:{$version}\r\n")
        ->and($card)->toEndWith("END:VCARD\r\n");

    // Katlanmış satırlar 75 oktetten uzun olmamalı (3.0 / 4.0)
    if ($version !== '2.1') {
        foreach (explode("\r\n", $card) as $line) {
            expect(strlen($line))->toBeLessThanOrEqual(75);
        }
    } else {
        expect($card)->toContain('ENCODING=QUOTED-PRINTABLE');
    }
    if ($version === '4.0') {
        expect($card)->toContain('TEL;VALUE=uri;TYPE="cell,voice":tel:+905321234567');
    }

    $parsed = $contacts->parseVcards($card);
    expect($parsed['records'])->toHaveCount(1)
        ->and($parsed['versions'])->toBe([$version => 1]);

    $r = $parsed['records'][0];
    expect($r['first_name'])->toBe('Şükrü Çağlar')
        ->and($r['last_name'])->toBe('Öztürk')
        ->and($r['company'])->toBe('IŞIK ELEKTRONİK; LTD')
        ->and($r['address'])->toBe('ATATÜRK CAD. NO:5, KADIKÖY İSTANBUL')
        ->and($r['email'])->toBe('sukru@example.com')
        ->and(ClientContacts::phoneKey($r['phone']))->toBe('5321234567');
})->with(ClientContacts::VCARD_VERSIONS);

test('real world vcards from iphone and android are parsed', function () {
    $iphone = "BEGIN:VCARD\r\nVERSION:3.0\r\nPRODID:-//Apple Inc.//iPhone OS 17.0//EN\r\nN:Yılmaz;Ayşe;;;\r\nFN:Ayşe Yılmaz\r\nORG:Kuzey Tekno;\r\n"
        ."item1.TEL;type=pref:+90 212 555 11 22\r\nTEL;type=CELL;type=VOICE:+90 (533) 444 55 66\r\nitem2.EMAIL;type=INTERNET;type=pref:ayse@\r\n kuzey.com\r\n"
        ."item3.ADR;type=HOME;type=pref:;;Bağdat Cd. 10;İstanbul;;34000;Türkiye\r\nEND:VCARD\r\n";

    // Android "Kişileri dışa aktar" (2.1, QP, yumuşak satır sonları)
    $android = "BEGIN:VCARD\r\nVERSION:2.1\r\nN;CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:=C3=96zdemir;Mehmet;;;\r\n"
        ."FN;CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:Mehmet =C3=96zdemir\r\nTEL;HOME:0216 333 22 11\r\nTEL;CELL:0544 777 66 55\r\n"
        ."ADR;HOME;CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:;;Uzun bir adres sat=C4=B1r=C4=B1 ki yumu=C5=9Fak sat=\r\n=C4=B1r sonu i=C3=A7erir;Ankara;;;\r\n"
        ."END:VCARD\r\n";

    $records = app(ClientContacts::class)->parseVcards($iphone.$android)['records'];

    expect($records)->toHaveCount(2);
    expect($records[0])->toMatchArray([
        'first_name' => 'Ayşe', 'last_name' => 'Yılmaz', 'company' => 'Kuzey Tekno',
        'email' => 'ayse@kuzey.com', 'address' => 'Bağdat Cd. 10, İstanbul, 34000, Türkiye',
    ]);
    expect($records[0]['phone'])->toBe('+90 (533) 444 55 66'); // cep telefonu tercih edilir
    expect($records[1])->toMatchArray([
        'first_name' => 'Mehmet', 'last_name' => 'Özdemir', 'phone' => '0544 777 66 55',
        'address' => 'Uzun bir adres satırı ki yumuşak satır sonu içerir, Ankara',
    ]);
});

test('google and outlook csv exports are recognized', function () {
    $contacts = app(ClientContacts::class);

    $google = "First Name,Middle Name,Last Name,Organization Name,E-mail 1 - Value,Phone 1 - Label,Phone 1 - Value\n"
        ."Zeynep,,Kara,Kara Ltd,zeynep@example.com,Mobile,+90 505 111 22 33\n";
    $outlook = "First Name;Last Name;Company;Mobile Phone;E-mail Address;Business Street\n"
        ."Ali;Veli;;0555 000 11 22;ali@example.com;Merkez Mah.\n";
    $turkish = "ad soyad;telefon;e-posta\nHasan Hüseyin Demir;05321112233;\n";

    expect($contacts->parse($google, 'google.csv')['records'][0])->toMatchArray(['first_name' => 'Zeynep', 'last_name' => 'Kara', 'company' => 'Kara Ltd', 'phone' => '+90 505 111 22 33']);
    expect($contacts->parse($outlook, 'outlook.csv')['records'][0])->toMatchArray(['first_name' => 'Ali', 'phone' => '0555 000 11 22', 'address' => 'Merkez Mah.']);
    expect($contacts->parse($turkish, 'liste.csv')['records'][0])->toMatchArray(['first_name' => 'Hasan Hüseyin', 'last_name' => 'Demir']);
});

test('import auto detects mixed vcard versions, skips duplicates and invalid contacts', function () {
    $user = User::factory()->create();
    $existing = Client::factory()->for($user)->create(['phone' => '0532 123 45 67', 'email' => null]);
    $contacts = app(ClientContacts::class);

    $other = new Client(['first_name' => 'Yeni', 'last_name' => 'Kişi', 'phone' => '0533 999 88 77']);
    $other->id = 999;
    $dup = new Client(['first_name' => 'Aynı', 'last_name' => 'Telefon', 'phone' => '+90 532 123 45 67', 'email' => 'yeni@example.com']);
    $dup->id = 998;

    $file = $contacts->vcard($other, '2.1')
        .$contacts->vcard($dup, '4.0')
        .$contacts->vcard($other, '3.0') // dosyada tekrar
        ."BEGIN:VCARD\r\nVERSION:3.0\r\nFN:Telefonsuz Kişi\r\nEND:VCARD\r\n";

    Livewire::actingAs($user)->test(ClientImport::class)
        ->set('file', contactsFile($file, 'rehber.vcf'))
        ->assertSet('detected', fn ($d) => str_starts_with($d, 'vCard') && str_contains($d, '2.1') && str_contains($d, '4.0'))
        ->assertSet('summary.new', 1)
        ->assertSet('summary.duplicate', 1)
        ->assertSet('summary.repeat', 1)
        ->assertSet('summary.invalid', 1)
        ->set('strategy', 'update')
        ->call('import')
        ->assertDispatched('client-saved');

    expect($user->clients()->count())->toBe(2)
        ->and($existing->fresh()->email)->toBe('yeni@example.com'); // güncelle: dolu alan yazıldı
});

test('export downloads csv and vcard in the chosen version and round-trips', function () {
    $owner = User::factory()->create();
    turkishClient($owner);
    Client::factory()->for($owner)->create(['phone' => '0544 222 33 44']);
    Client::factory()->create(['first_name' => 'Yabancı']);

    $vcf = $this->actingAs($owner)->get(route('clients.export', ['format' => 'vcard', 'version' => '2.1']))
        ->assertOk()->streamedContent();
    expect(substr_count($vcf, 'BEGIN:VCARD'))->toBe(2)
        ->and($vcf)->toContain('VERSION:2.1')
        ->and($vcf)->not->toContain('Yabanc');

    $csv = $this->actingAs($owner)->get(route('clients.export', ['format' => 'csv']))->assertOk()->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBF".'first_name,last_name,phone');

    $target = User::factory()->create();
    Livewire::actingAs($target)->test(ClientImport::class)->set('file', contactsFile($vcf, 'yedek.vcf'))->call('import');
    expect($target->clients()->count())->toBe(2)
        ->and($target->clients()->where('last_name', 'Öztürk')->first()->company)->toBe('IŞIK ELEKTRONİK; LTD');
});

test('single client vcard download respects ownership and version', function () {
    $user = User::factory()->create();
    $client = turkishClient($user);

    $this->actingAs($user)->get(route('clients.vcard', [$client, 'version' => '4.0']))
        ->assertOk()
        ->assertHeader('content-type', 'text/vcard; charset=UTF-8')
        ->assertSee('VERSION:4.0', false);

    $this->actingAs(User::factory()->create())->get(route('clients.vcard', $client))->assertNotFound();
});

test('client card offers vcard qr in all versions with the preferred default', function () {
    $user = User::factory()->create();
    $client = turkishClient($user);
    \App\Support\Settings::set($user, 'clients.vcard_version', '4.0');

    Livewire::actingAs($user)->test(ClientsDetail::class)
        ->call('load', $client->id)
        ->assertViewHas('vcardVersion', '4.0')
        ->assertViewHas('vcards', fn ($v) => array_keys($v) === ClientContacts::VCARD_VERSIONS && str_contains($v['2.1'], 'VERSION:2.1'))
        ->assertSee('Rehbere ekle (vCard)');

    $this->actingAs($user)->get(route('settings', 'musteriler'))->assertOk()->assertSee('vCard 3.0');
});
