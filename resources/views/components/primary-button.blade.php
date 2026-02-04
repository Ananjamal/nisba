<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary transition-all active:scale-95 disabled:opacity-50']) }}>
    {{ $slot }}
</button>