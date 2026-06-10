<div
    {{ $attributes->merge([
        'class' => 'bg-white rounded-xl border shadow-sm'
    ]) }}
>
    {{ $slot }}
</div>
