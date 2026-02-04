@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 px-2']) }}>
    {{ $value ?? $slot }}
</label>