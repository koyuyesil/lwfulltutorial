<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ClientContacts;
use App\Services\DeviceCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientContactsController extends Controller
{
    /** Tüm müşteriler: ?format=csv | vcard & ?version=2.1|3.0|4.0 */
    public function export(Request $request, ClientContacts $contacts): StreamedResponse
    {
        $data = $request->validate([
            'format' => ['nullable', 'in:csv,vcard'],
            'version' => ['nullable', 'in:'.implode(',', ClientContacts::VCARD_VERSIONS)],
        ]);
        $user = $request->user();
        $stamp = now()->format('Y-m-d-His');

        if (($data['format'] ?? 'csv') === 'vcard') {
            $version = $data['version'] ?? '3.0';

            return response()->streamDownload(function () use ($contacts, $user, $version) {
                foreach ($contacts->exportVcards($user, $version) as $card) {
                    echo $card;
                }
            }, "musteriler-vcard-{$version}-{$stamp}.vcf", ['Content-Type' => 'text/vcard; charset=UTF-8']);
        }

        return response()->streamDownload(function () use ($contacts, $user) {
            DeviceCatalog::writeCsv($contacts->exportCsvRows($user), fopen('php://output', 'w'));
        }, "musteriler-{$stamp}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Tek müşterinin vCard'ı. */
    public function vcard(Request $request, Client $client, ClientContacts $contacts)
    {
        abort_unless($client->user_id === $request->user()->id, 404);

        $version = in_array($request->query('version'), ClientContacts::VCARD_VERSIONS, true) ? $request->query('version') : '3.0';
        $filename = Str::slug($client->full_name ?: 'musteri').'.vcf';

        return response($contacts->vcard($client, $version), 200, [
            'Content-Type' => 'text/vcard; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
