<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Üst çubuktaki genel arama: servis kaydı, müşteri, cihaz modeli ve görev.
 * Yalnızca oturumdaki kullanıcının kayıtlarında arar.
 */
class Search extends Component
{
    public string $search = '';

    public function render()
    {
        $term = trim($this->search);
        $groups = [];

        if (mb_strlen($term) >= 2) {
            $user = Auth::user();
            $like = '%'.$term.'%';
            $digits = preg_replace('/\D/', '', $term);
            $concat = fn ($a, $b) => DB::getDriverName() === 'mysql' ? "CONCAT($a, ' ', $b)" : "($a || ' ' || $b)";

            $groups['tickets'] = $user->tickets()
                ->with('clientProduct.client:id,first_name,last_name', 'clientProduct.product:id,brand,model_name')
                ->where(fn ($q) => $q->where('tracking_code', 'like', '%'.strtoupper($term).'%')
                    ->orWhere('problem', 'like', $like)
                    ->orWhereHas('clientProduct', fn ($d) => $d->where('imei', 'like', $like)->orWhere('serial', 'like', $like)))
                ->latest('id')->limit(5)->get();

            $groups['clients'] = $user->clients()
                ->where(function ($q) use ($like, $digits, $concat, $term) {
                    $q->whereRaw($concat('first_name', 'last_name').' like ?', [$like])
                        ->orWhere('company', 'like', '%'.Client::trUpper($term).'%');
                    if (strlen($digits) >= 3) {
                        $q->orWhereRaw(Client::phoneDigitsSql().' like ?', ['%'.$digits.'%']);
                    }
                })
                ->limit(5)->get();

            $groups['products'] = $user->products()
                ->where(fn ($q) => $q->whereRaw($concat('brand', 'model_name').' like ?', [$like])->orWhere('model_number', 'like', $like))
                ->limit(5)->get();

            $groups['tasks'] = $user->tasks()
                ->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like))
                ->limit(5)->get();

            $groups = array_filter($groups, fn ($items) => $items->isNotEmpty());
        }

        return view('livewire.search', ['groups' => $groups, 'term' => $term]);
    }
}
