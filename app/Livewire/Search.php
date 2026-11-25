<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;

class Search extends Component
{
    // Url aspect yada attribute yazıldığı değişkenin urlden de alınabileceğini ifade eder.
    //yani şpost da aynı değişkeni arar varsa buna eşitler.
    //as url değişkeni farklı kullanılacaksa.
    //keep true sayfada herzaman query gözükür.
    //history aramaları tarayıcı geçmişine ekler
    #[Url(as: 'q', keep: true, history: true)]
    public $search;
    public function render()
    {
        $results = [];

        $term = trim((string) $this->search);

        if (mb_strlen($term) > 2) {
            // orWhere'ler gruplanmazsa user_id filtresi devre dışı kalır ve başka kullanıcıların görevleri sızar.
            $results = auth()->user()->tasks()
                ->where(function ($query) use ($term) {
                    $query->where('title', 'like', '%'.$term.'%')
                        ->orWhere('description', 'like', '%'.$term.'%');
                })
                ->limit(10)
                ->get();
        }

        return view('livewire.search', compact('results'));
    }
}
