@props([
    'active' => false,
])

<span
    {{ $attributes->merge([
        'class' => $active
            ? 'px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm'
            : 'px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm'
    ]) }}
>
    {{ $slot }}
</span>
