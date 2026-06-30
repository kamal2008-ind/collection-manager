@props(['book', 'selected' => []])

<div
    class="
        rounded-xl
        p-4
        shadow-sm
        {{ in_array($book->id, $selected ?? []) ? 'border-blue-500 ring-2 ring-blue-200' : 'border' }}
        bg-white
    ">
    @php
        $isOwner = auth()->id() === $book->user_id;
        $bookUrl = url('/u/' . $book->user->username . '/books/' . $book->slug);
    @endphp

    {{-- Header --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-2">
            @if ($isOwner)
                <input type="checkbox" value="{{ $book->id }}" wire:model.live="selected"
                    class="rounded border-gray-400" />
            @endif

            <span title="{{ $book->title }}"
                class="truncate max-w-[240px] font-medium hover:shadow-md hover:border-gray-300 transition">
                {{ $book->title }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            @if ($isOwner)
                <button title="{{ $book->is_favorite ? 'Remove Favorite' : 'Add Favorite' }}"
                    wire:click="toggleFavorite({{ $book->id }})">
                    @if ($book->is_favorite)
                        ⭐
                    @else
                        <span class="text-2xl">☆</span>
                    @endif
                </button>

                <button title="Edit" wire:click="editBook({{ $book->id }})">
                    ✏️
                </button>

                <button title="Move to trash" wire:click="confirmDelete({{ $book->id }})">
                    🗑️
                </button>

                <div class="relative">
                    <button type="button"
                        @click.stop="activeMenu = activeMenu === 'book-{{ $book->id }}' ? null : 'book-{{ $book->id }}'"
                        class="rounded p-1 hover:bg-gray-100" title="More actions">
                        ⋮
                    </button>

                    <div x-show="activeMenu === 'book-{{ $book->id }}'" @click.outside="activeMenu = null"
                        x-transition
                        class="absolute right-0 z-[9999] mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">
                        <div class="my-1 border-t"></div>

                        <button type="button" wire:click="openAttachToDrawer({{ $book->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>📎</span>
                            <span>Attach To</span>
                        </button>

                        <button type="button" wire:click="openDetachFromDrawer({{ $book->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>⛓️‍💥</span>
                            <span>Detach From</span>
                        </button>

                        <div class="my-1 border-t"></div>

                        <button type="button" wire:click="copyBookUrl({{ $book->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>🔗</span>
                            <span>Copy link</span>
                        </button>

                        <button type="button" wire:click="bookStatistics({{ $book->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>📊</span>
                            <span>Statistics</span>
                        </button>

                        <button type="button" wire:click="bookSettings({{ $book->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>⚙️</span>
                            <span>Settings</span>
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

    {{-- Mini Show Layout --}}
    <div class="mt-4 flex gap-4">
        <div class="shrink-0">
            @if ($book->cover_image)
                <img src="{{ asset('storage/' . $book->cover_image) }}"
                    class="h-32 w-24 rounded-lg border object-cover" alt="{{ $book->title }}">
            @else
                <div class="flex h-32 w-24 items-center justify-center rounded-lg border bg-gray-50 text-3xl">
                    🎬
                </div>
            @endif
        </div>

        <div class="min-w-0 flex flex-1 flex-col">
            <div class="mt-1 truncate text-xs text-gray-500">
                {{ $book->year ?: 'N/A' }}
                <span class="px-1">•</span>
                <span title="Author : {{ trim($book->author) }}">{{ trim($book->author) ?: 'N/A' }}</span>
                <span class="px-1">•</span>
                <span title="Publisher : {{ trim($book->publisher) }}">{{ trim($book->publisher) ?: 'N/A' }}</span>
            </div>

            @if ($book->description)
                <p class="mt-3 line-clamp-3 text-sm leading-6 text-gray-600" title="{{ $book->description }}">
                    {{ $book->description }}
                </p>
            @else
                <p class="mt-3 text-sm text-gray-400">
                    No description added.
                </p>
            @endif

            <div class="mt-auto flex flex-wrap sm:flex-nowrap items-center gap-2 text-xs text-gray-600">
                <span class="whitespace-nowrap" title="Attached workspaces">
                    🏢
                    {{-- <span class="rounded bg-purple-100 px-1 py-1 text-xs text-purple-700"> --}}
                    Workspaces ({{ $book->workspaces_count ?? 0 }})
                    {{-- </span> --}}
                </span>

                <span class="whitespace-nowrap" title="Attached collections">
                    📁
                    {{-- <span class="rounded bg-yellow-100 px-1 py-1 text-xs text-yellow-700"> --}}
                    Collections ({{ $book->collections_count ?? 0 }})
                    {{-- </span> --}}
                </span>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-4 flex items-center justify-between text-sm">
        <div class="flex flex-wrap items-center gap-2">
            <x-status-badge :visibility="$book->visibility" :shared="($book->shares_count ?? 0) > 0" />

            <x-owner-badge :userid="$book->user_id" :username="$book->user->username" />
        </div>

        <div class="flex gap-2">
            <x-card-footer-meta :visibility="$book->visibility" :assetId="$book->id" :isOwner="$isOwner" :likeCount="$book->likes_count ?? 0"
                :likedByUser="$book->isLikedBy(auth()->user())" :shareCount="$book->shares_count" :assetUrl="$bookUrl" />
        </div>
    </div>
</div>
