<div class="relative">
    <div class="absolute inset-x-0 top-0 -z-10 h-96 bg-[radial-gradient(60%_60%_at_50%_0%,rgba(99,102,241,0.15),transparent)]"></div>
    <div class="mx-auto max-w-2xl px-4 py-14 sm:px-6">
        <div class="text-center">
            <span class="inline-grid h-14 w-14 place-items-center rounded-2xl bg-linear-to-br from-indigo-500 to-violet-600 text-2xl text-white shadow-lg shadow-indigo-500/30">★</span>
            <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900 dark:text-white">Deneyiminizi puanlayın</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400">
                {{ $ticket->clientProduct->client->first_name }}, {{ $ticket->clientProduct->product->brand }} {{ $ticket->clientProduct->product->model_name }} cihazınızın servisi için
                <strong>{{ $ticket->user->companyName() }}</strong>'i değerlendirir misiniz?
            </p>
        </div>

        <div class="mt-10 rounded-2xl bg-white p-6 shadow-xl shadow-slate-200/60 ring-1 ring-slate-200 dark:bg-slate-900 dark:shadow-none dark:ring-white/10">
            @if (! is_null($ticket->survey_score))
                <div class="py-6 text-center">
                    <p class="text-5xl">🙏</p>
                    <p class="mt-3 text-xl font-bold text-slate-900 dark:text-white">Teşekkür ederiz!</p>
                    <p class="mt-1 text-slate-600 dark:text-slate-400">Değerlendirmeniz ({{ $ticket->survey_score }}/10) kaydedildi. Görüşleriniz hizmetimizi geliştirmemize yardımcı oluyor.</p>
                </div>
            @else
                <form wire:submit="submit">
                    <p class="text-center font-semibold text-slate-800 dark:text-slate-100">Bizi bir arkadaşınıza tavsiye etme olasılığınız nedir?</p>
                    <div class="mt-4 grid grid-cols-11 gap-1">
                        @foreach (range(0, 10) as $n)
                            <button type="button" wire:click="$set('score', {{ $n }})" aria-pressed="{{ $score === $n ? 'true' : 'false' }}"
                                @class(['aspect-square rounded-lg text-sm font-black ring-1 transition sm:text-base',
                                    'bg-rose-500 text-white ring-rose-500' => $score === $n && $n <= 6,
                                    'bg-amber-400 text-amber-950 ring-amber-400' => $score === $n && $n >= 7 && $n <= 8,
                                    'bg-emerald-500 text-white ring-emerald-500' => $score === $n && $n >= 9,
                                    'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50 dark:bg-white/5 dark:text-slate-200 dark:ring-white/10' => $score !== $n])>{{ $n }}</button>
                        @endforeach
                    </div>
                    <div class="mt-2 flex justify-between text-xs text-slate-500"><span>Hiç olası değil</span><span>Kesinlikle</span></div>
                    @error('score') <p class="mt-3 text-center text-sm text-rose-600">{{ $message }}</p> @enderror

                    <label class="mt-6 block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Eklemek istedikleriniz <span class="font-normal text-slate-400">(isteğe bağlı)</span></span>
                        <textarea wire:model="comment" rows="3" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-white/10 dark:bg-slate-950"
                            placeholder="Neyi beğendiniz, neyi daha iyi yapabiliriz?"></textarea>
                    </label>
                    <button type="submit" class="mt-5 w-full rounded-xl bg-indigo-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-500">Gönder</button>
                </form>
            @endif
        </div>
    </div>
</div>
