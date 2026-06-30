<div class="relative rounded-xl border bg-white overflow-visible">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left"></th>

                <th class="p-3 text-left w-[30%]">
                    Workspace
                </th>
                <th class="p-3 text-center w-[10%]">
                    Owner
                </th>
                <th class="p-3 text-center w-[10%]">
                    Collections
                </th>
                <th class="p-3 text-center w-[10%]">
                    Movies
                </th>
                <th class="p-3 text-center w-[10%]">
                    Books
                </th>
                <th class="p-3 text-center w-[15%]">
                    Status
                </th>
                <th class="p-3 text-right w-[15%]">
                    Actions
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse($workspaces as $workspace)
                @php
                    $isOwner = auth()->id() === $workspace->user_id;
                    $workspaceUrl = url('/u/' . $workspace->user->username . '/workspaces/' . $workspace->slug);
                @endphp

                <tr wire:key="workspace-table-{{ $workspace->id }}" class="border-t hover:bg-gray-50">

                    {{-- Select --}}
                    <td class="p-3">
                        @if ($isOwner)
                            <input type="checkbox" value="{{ $workspace->id }}" wire:model.live="selected">
                        @endif
                    </td>
                    {{-- Workspace --}}
                    <td class="p-3 font-medium" title="{{ $workspace->name }}">
                        {{ $workspace->name }}
                    </td>
                    {{-- Owner --}}
                    <td class="p-3 text-center">
                        <x-owner-badge :userid="$workspace->user_id" :username="$workspace->user->username" :view="$view" />
                    </td>
                    {{-- Collections --}}
                    <td class="p-3 text-center">
                        {{ $workspace->collections_count ?? 0 }}
                    </td>
                    {{-- Movies --}}
                    <td class="p-3 text-center">
                        {{ $workspace->movies_count ?? 0 }}
                    </td>
                    {{-- Books --}}
                    <td class="p-3 text-center">
                        {{ $workspace->books_count ?? 0 }}
                    </td>
                    {{-- Status --}}
                    <td class="p-3">
                        <div class="flex items-center justify-center gap-3">
                            <x-status-badge :visibility="$workspace->visibility" :shared="($workspace->shares_count ?? 0) > 0" :view="$view" />

                            <x-card-footer-meta :visibility="$workspace->visibility" :assetId="$workspace->id" :isOwner="$isOwner" :likeCount="$workspace->likes_count ?? 0"
                                :likedByUser="$workspace->isLikedBy(auth()->user())" :shareCount="$workspace->shares_count" :assetUrl="$workspaceUrl" />
                        </div>
                    </td>
                    {{-- Actions --}}
                    <td class="p-3">
                        <div class="flex justify-end items-center gap-3">
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
                                <button title="Move to Trash" wire:click="confirmDelete({{ $workspace->id }})">
                                    🗑️
                                </button>
                                <div class="relative" x-on:close-more-menu.window="activeMenu = null">
                                    <button
                                        @click.stop="activeMenu = activeMenu === {{ $workspace->id }} ? null : {{ $workspace->id }}"
                                        class="rounded
                                                p-1 hover:bg-gray-100"
                                        title="More actions">
                                        ⋮
                                    </button>

                                    <div x-show="activeMenu === {{ $workspace->id }}"
                                        @click.outside="activeMenu = null" x-transition
                                        class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">
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

                                        <div class="border-t"></div> {{-- Duplicate --}}
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
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-6 text-center">
                        <x-empty-state icon="🎬" title="No workspaces found"
                            message="Try changing your search/filter/access mode or create a new workspace." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
