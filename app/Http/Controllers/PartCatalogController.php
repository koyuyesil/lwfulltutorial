<?php

namespace App\Http\Controllers;

use App\Services\DeviceCatalog;
use App\Services\PartCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartCatalogController extends Controller
{
    public function export(Request $request, PartCatalog $catalog): StreamedResponse
    {
        $user = $request->user();

        return response()->streamDownload(function () use ($catalog, $user) {
            DeviceCatalog::writeCsv($catalog->exportRows($user), fopen('php://output', 'w'));
        }, 'parcalar-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
