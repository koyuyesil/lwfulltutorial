<?php

use App\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Eski kod "İ" harfini mb_convert_case ile "i̇" (i + birleşik nokta) yapıyordu.
     * Ad/soyad Türkçe kurallarla yeniden biçimlendirilir.
     */
    public function up(): void
    {
        DB::table('clients')->orderBy('id')->each(function ($row) {
            $first = Client::trTitle((string) $row->first_name);
            $last = Client::trTitle((string) $row->last_name);

            if ($first !== $row->first_name || $last !== $row->last_name) {
                DB::table('clients')->where('id', $row->id)->update(['first_name' => $first, 'last_name' => $last]);
            }
        });
    }

    public function down(): void
    {
        // Geri alınacak bir şey yok: yalnızca biçim düzeltmesi
    }
};
