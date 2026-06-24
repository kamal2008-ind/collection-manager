@props([
    'model',
    'placeholder' => 'Search...',
])

<div class="relative">
    <input
        type="text"
        wire:model.live.debounce.500ms="{{ $model }}"
        placeholder="{{ $placeholder }}"
        class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-10 text-base focus:border-blue-300 focus:ring-blue-300"
    >

    @if ($attributes->wire('model')->value())
        {{-- ignore this --}}
    @endif

    <button
        type="button"
        wire:click="$set('{{ $model }}', '')"
        x-show="$wire.{{ $model }}?.length > 0"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-xl text-gray-400 hover:text-gray-700"
        title="Clear search"
    >
        ×
    </button>
</div>
