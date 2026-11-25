<?php

namespace App\Http\Controllers;

use App\Services\BoardIdCatalog;
use App\Services\DeviceCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BoardIdCatalogController extends Controller
{
    /** Kullanıcının Board ID kütüphanesi (orijinal direnç değerleri dahil) CSV olarak. */
    public function export(Request $request, BoardIdCatalog $catalog): StreamedResponse
    {
        $user = $request->user();

        return response()->streamDownload(function () use ($catalog, $user) {
            DeviceCatalog::writeCsv($catalog->exportRows($user), fopen('php://output', 'w'));
        }, 'board-id-kutuphanesi-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Servis direnç tablosundan dönüştürülmüş örnek veri. */
    public function sample(): BinaryFileResponse
    {
        return response()->download(BoardIdCatalog::samplePath(), 'board-id-ornek.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
