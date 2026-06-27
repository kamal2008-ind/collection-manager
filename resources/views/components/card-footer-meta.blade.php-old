@props([
    'visibility' => 'private',
    'assetId' => 1,
    'isOwner' => false,
    'likeCount' => 0,
    'shareCount' => 0,
    'assetUrl' => '#',
    'module' => 'workspace',
])
<span title="Likes">
    ❤️ {{ $likeCount }}
</span>
@if ($visibility === 'public')
    <button type="button" title="Copy Link" wire:click="copyShareLink({{ $assetId }})">
        🔗
    </button>
@elseif($isOwner)
    <button type="button" title="Share privately with user(s)" wire:click="openShareDrawer({{ $assetId }})">
        🤝 {{ $shareCount }}
    </button>
@else
    <span title="Shared count">
        🤝 {{ $shareCount ?? 0 }}
    </span>
@endif
<a href="{{ $assetUrl }}" target="_blank" title="Open link">
    ↗️
</a>
