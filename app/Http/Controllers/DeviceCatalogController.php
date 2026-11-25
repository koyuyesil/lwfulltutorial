<?php

namespace App\Http\Controllers;

use App\Services\DeviceCatalog;
use App\Support\SampleDeviceCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeviceCatalogController extends Controller
{
    /** Kullanıcının tüm cihaz modelleri (görseller dahil) CSV olarak. */
    public function export(Request $request, DeviceCatalog $catalog): StreamedResponse
    {
        $user = $request->user();
        $filename = 'cihaz-modelleri-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($catalog, $user) {
            DeviceCatalog::writeCsv($catalog->exportRows($user), fopen('php://output', 'w'));
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Popüler markaların gerçek modelleriyle örnek içe aktarma şablonu. */
    public function sample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            DeviceCatalog::writeCsv(SampleDeviceCatalog::csvRows(), fopen('php://output', 'w'));
        }, 'cihaz-modelleri-ornek.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
