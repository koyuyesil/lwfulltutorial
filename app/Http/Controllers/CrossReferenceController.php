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

    /** Sayfada seçilenler: sabit aralıklı metin tablo (txt) ya da Excel (xlsx) */
    public function selection(Request $request, CrossReferenceCatalog $catalog, string $format)
    {
        $data = $request->validate([
            'view' => ['required', 'in:components,devices'],
            'ids' => ['required', 'string', 'max:20000', 'regex:/^\d+(,\d+)*$/'],
        ], ['ids.required' => 'Dışa aktarmak için en az bir satır seçin.']);

        $rows = $catalog->selectionRows($request->user(), $data['view'], explode(',', $data['ids']));
        abort_if(count($rows) < 2, 404);

        $name = 'capraz-referans-'.($data['view'] === 'devices' ? 'cihazlar' : 'bilesenler').'-'.now()->format('Y-m-d-His');

        return $format === 'xlsx'
            ? response(\App\Support\TableExport::xlsx($rows, $data['view'] === 'devices' ? 'Cihazlar' : 'Bileşenler'), 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$name.'.xlsx"',
            ])
            : response(\App\Support\TableExport::text($rows), 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$name.'.txt"',
            ]);
    }

    public function sample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            DeviceCatalog::writeCsv(SampleCrossReference::csvRows(), fopen('php://output', 'w'));
        }, 'capraz-referans-ornek.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
