<?php

namespace App\Http\Controllers;

use App\Services\CrossReferenceCatalog;
use App\Support\TableExport;
use Illuminate\Http\Request;

class CrossReferenceController extends Controller
{
    /** Tüm çapraz referans parçaları: cihazları ve muadilleriyle JSON */
    public function export(Request $request, CrossReferenceCatalog $catalog)
    {
        return response()->json($catalog->export($request->user()), 200, [
            'Content-Disposition' => 'attachment; filename="capraz-referans-'.now()->format('Y-m-d-His').'.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Sayfada seçilenler: sabit aralıklı metin tablo (txt), Excel (xlsx) ya da JSON */
    public function selection(Request $request, CrossReferenceCatalog $catalog, string $format)
    {
        $data = $request->validate([
            'view' => ['required', 'in:parts,devices'],
            'ids' => ['required', 'string', 'max:20000', 'regex:/^\d+(,\d+)*$/'],
        ], ['ids.required' => 'Dışa aktarmak için en az bir satır seçin.']);

        $ids = explode(',', $data['ids']);
        $name = 'capraz-referans-'.($data['view'] === 'devices' ? 'cihazlar' : 'parcalar').'-'.now()->format('Y-m-d-His');

        if ($format === 'json') {
            $partIds = $data['view'] === 'devices'
                ? \Illuminate\Support\Facades\DB::table('part_product')->whereIn('product_id', $request->user()->products()->whereIn('id', $ids)->select('id'))->pluck('part_id')->all()
                : $request->user()->parts()->whereIn('id', $ids)->pluck('id')->all();
            abort_if(! $partIds, 404);

            return response()->json($catalog->export($request->user(), $partIds), 200, [
                'Content-Disposition' => 'attachment; filename="'.$name.'.json"',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $rows = $catalog->selectionRows($request->user(), $data['view'], $ids);
        abort_if(count($rows) < 2, 404);

        return $format === 'xlsx'
            ? response(TableExport::xlsx($rows, $data['view'] === 'devices' ? 'Cihazlar' : 'Parçalar'), 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$name.'.xlsx"',
            ])
            : response(TableExport::text($rows), 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$name.'.txt"',
            ]);
    }
}
