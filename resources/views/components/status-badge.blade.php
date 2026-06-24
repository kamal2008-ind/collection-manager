@props([
    'visibility' => 'private',
    'shared' => false,
    'view' => 'card',
])
@if ($view === 'card')
    @if ($visibility === 'public')
        <span class="inline-flex items-center gap-1 rounded bg-green-100 px-2 py-1 text-xs text-green-700">
            🌍 Public
        </span>
    @elseif ($shared)
        <span class="inline-flex items-center gap-1 rounded bg-blue-100 px-2 py-1 text-xs text-blue-700">
            👥 Shared
        </span>
    @else
        <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">
            🔒 Private
        </span>
    @endif
@else
    @if ($visibility === 'public')
        <span title="Public">🌍</span>
    @elseif ($shared)
        <span title="Shared">👥</span>
    @else
        <span title="Private">🔒</span>
    @endif
@endif
