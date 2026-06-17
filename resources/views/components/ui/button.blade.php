@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
$classes = match($variant) {
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700',
    'secondary' => 'border border-gray-300 bg-white hover:bg-gray-50',
    'danger' => 'bg-red-600 text-white hover:bg-red-700',
    default => 'bg-blue-600 text-white hover:bg-blue-700',
};
@endphp

<button
    type="{{ $type }}"
    @disabled($attributes->get('disabled'))
    {{ $attributes->merge([
        'class' => "px-4 py-2 rounded-lg transition font-medium {$classes} disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500 disabled:hover:bg-gray-300"
    ]) }}
>
    {{ $slot }}
</button>
