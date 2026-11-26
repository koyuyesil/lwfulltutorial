<?php

namespace App\Http\Controllers;

use App\Services\CrossReferenceCatalog;
use App\Services\DeviceCatalog;
use App\Support\SampleCrossReference;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrossReferenceController extends Controller
{
    public function export(Request $request, CrossReferenceCatalog $catalog): StreamedResponse
    {
        $user = $request->user();

        return response()->streamDownload(function () use ($catalog, $user) {
            DeviceCatalog::writeCsv($catalog->exportRows($user), fopen('php://output', 'w'));
        }, 'capraz-referans-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function sample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            DeviceCatalog::writeCsv(SampleCrossReference::csvRows(), fopen('php://output', 'w'));
        }, 'capraz-referans-ornek.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
