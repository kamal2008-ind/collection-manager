@props([
    'userid' => 1,
    'username' => 'Admin',
    'view' => 'card',
])
@if ($view === 'card')
    <span class="text-xs text-gray-500" title="Owner">
        👤
        {{ $userid === auth()->id() ? 'Me' : '@' . $username }}
    </span>
@else
    <div class="text-sm text-gray-500">
        👤
        {{ $userid === auth()->id() ? 'Me' : '@' . $username }}
    </div>
@endif
