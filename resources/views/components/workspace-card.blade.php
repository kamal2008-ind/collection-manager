<div
    class="
        rounded-xl
        p-4
        shadow-sm
        {{ in_array($workspace->id, $selected ?? []) ? 'border-blue-500 ring-2 ring-blue-200' : 'border' }}
        bg-white
    ">
    @php
        $isOwner = auth()->id() === $workspace->user_id;
        $workspaceUrl = url('/u/' . $workspace->user->username . '/workspaces/' . $workspace->slug);
    @endphp
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0">
            @if ($isOwner)
                <input type="checkbox" value="{{ $workspace->id }}" wire:model.live="selected"
                    class="rounded border-gray-400" />
            @endif
            <span title="{{ $workspace->name }}"
                class="truncate max-w-[180px] font-medium hover:shadow-md hover:border-gray-300 transition">
                {{ $workspace->name }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            @if ($isOwner)
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
                <div class="relative" x-on:close-more-menu.window="activeMenu = null">
                    <button type="button"
                        @click.stop="activeMenu = activeMenu === 'workspace-{{ $workspace->id }}' ? null : 'workspace-{{ $workspace->id }}'"
                        class="rounded p-1 hover:bg-gray-100" title="More actions">
                        ⋮
                    </button>

                    <div x-show="activeMenu === 'workspace-{{ $workspace->id }}'" @click.outside="activeMenu = null"
                        x-transition
                        class="absolute right-0 z-[9999] mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">
                        <div class="border-t"></div>
                        <button type="button" wire:click="openAddItemsDrawer({{ $workspace->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>➕</span>
                            <span>Add Items</span>
                        </button>
                        <button type="button" wire:click="openRemoveItemsDrawer({{ $workspace->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-red-50 text-red-600">
                            <span>➖</span>
                            <span>Remove Items</span>
                        </button>

                        <div class="border-t"></div>
                        {{-- Duplicate --}}
                        <button type="button" wire:click="duplicateWorkspace({{ $workspace->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>📋</span>
                            <span>Duplicate</span>
                        </button>

                        {{-- Copy Link --}}
                        <button type="button" wire:click="copyWorkspaceUrl({{ $workspace->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>🔗</span>
                            <span>Copy link</span>
                        </button>
                    </div>
                </div>
            @else
                <span class="rounded bg-blue-50 px-2 py-1 text-xs text-blue-700">
                    View only
                </span>
            @endif
        </div>
    </div>

    {{-- Body --}}
    <div class="mt-4 space-y-2 text-sm items-center">
        <div>
            📁 Collections ({{ $workspace->collections_count ?? 0 }})
        </div>
        <div>
            🎬 Movies ({{ $workspace->movies_count ?? 0 }})
        </div>
        <div>
            📘 Books (0)
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-4 flex items-center justify-between text-sm">
        <div>
            <x-status-badge :visibility="$workspace->visibility" :shared="($workspace->shares_count ?? 0) > 0"/>

            <x-owner-badge :userid="$workspace->user_id" :username="$workspace->user->username" />
        </div>

        <div class="flex gap-2">
            <x-card-footer-meta :visibility="$workspace->visibility" :assetId="$workspace->id" :isOwner="$isOwner" :shareCount="$workspace->shares_count"
                :assetUrl="$workspaceUrl" />
        </div>
    </div>
</div>
