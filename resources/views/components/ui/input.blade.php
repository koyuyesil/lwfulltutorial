@props(['invalid' => false])

<input {{ $attributes->class([
    'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset dark:bg-slate-950 dark:text-white',
    'ring-slate-300 focus:ring-indigo-600 dark:ring-white/10 dark:focus:ring-indigo-500' => ! $invalid,
    'ring-rose-400 focus:ring-rose-500' => $invalid,
]) }}>
