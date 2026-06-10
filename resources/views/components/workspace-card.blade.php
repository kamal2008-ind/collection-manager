<div
    class="
        rounded-xl
        p-4
        shadow-sm
        {{ in_array($workspace->id, $selected ?? []) ? 'border-blue-500 ring-2 ring-blue-200' : 'border' }}
        bg-white
    ">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0">
            <input type="checkbox" value="{{ $workspace->id }}" wire:model.live="selected"
                class="rounded border-gray-400" />

            <span title="{{ $workspace->name }}"
                class="truncate max-w-[180px] font-medium hover:shadow-md hover:border-gray-300 transition">
                {{ $workspace->name }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            <button title="{{ $workspace->is_favorite ? 'Remove Favorite' : 'Add Favorite' }}"
                wire:click="toggleFavorite({{ $workspace->id }})">
                @if ($workspace->is_favorite)
                    ⭐
                @else
                    <span class="text-2xl">☆</span>
                @endif
            </button>
            <button title="Edit" wire:click="editWorkspace({{ $workspace->id }})">
                ✏️
            </button>
            <button title="Move to trash" wire:click="confirmDelete({{ $workspace->id }})">
                🗑️
            </button>
            <button title="More...">
                ⋮
            </button>
        </div>
    </div>

    {{-- Body --}}
    <div class="mt-4 space-y-2 text-sm">
        <div>
            Collections (0)
        </div>
        <div>
            Movies (0)
        </div>
        <div>
            Books (0)
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-4 flex items-center justify-between text-sm">
        <div>
            @if ($workspace->visibility === 'public')
                🌍 Public
            @else
                🔒 Private
            @endif
        </div>

        <div class="flex gap-4">
            <span>
                👍 0
            </span>
            <button
                title="{{ $workspace->visibility === 'public' ? 'Copy Share Link' : 'Make workspace public to share' }}"
                @if ($workspace->visibility === 'public') wire:click="copyShareLink({{ $workspace->id }})" @endif
                class="{{ $workspace->visibility === 'public' ? '' : 'opacity-40 cursor-not-allowed' }}">
                ↗
            </button>
        </div>
    </div>
</div>
