{{--
    Kopyalanabilir kod (takip kodu vb.). href verilirse kod bağlantıdır; yanındaki düğme kopyalar.
    href yoksa kodun kendisine tıklamak da kopyalar. plain: başka bir bağlantının içindeyken kod düz metindir, yalnızca simge kopyalar.
--}}
@props(['code', 'href' => null, 'navigate' => true, 'plain' => false, 'textClass' => 'font-bold text-slate-900 dark:text-white'])

<span x-data="copyCode(@js((string) $code))" {{ $attributes->class('group/code inline-flex min-w-0 items-center gap-1 align-middle font-mono') }}>
    @if ($href)
        <a href="{{ $href }}" @if ($navigate) wire:navigate @endif data-code class="truncate hover:text-indigo-600 dark:hover:text-indigo-300 {{ $textClass }}">{{ $code }}</a>
    @elseif ($plain)
        <span data-code class="truncate {{ $textClass }}">{{ $code }}</span>
    @else
        <button type="button" @click.stop.prevent="copy()" class="truncate text-left select-all {{ $textClass }}" title="Kopyala"><span data-code>{{ $code }}</span></button>
    @endif
    <button type="button" @click.stop.prevent="copy()" title="Kodu kopyala" aria-label="{{ $code }} kodunu kopyala"
        :class="copied ? 'text-emerald-500 opacity-100' : 'text-slate-400 opacity-50 group-hover/code:opacity-100 hover:text-indigo-600 dark:hover:text-indigo-300'"
        class="shrink-0 rounded p-0.5 transition hover:bg-slate-100 focus:opacity-100 dark:hover:bg-white/10">
        <svg x-show="! copied" class="h-[1em] w-[1em]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
        <svg x-show="copied" x-cloak class="h-[1em] w-[1em]" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
    </button>
</span>
