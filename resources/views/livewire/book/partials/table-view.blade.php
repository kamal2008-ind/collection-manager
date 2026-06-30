<div class="relative rounded-xl border bg-white overflow-visible">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left"></th>
                <th class="p-3 text-left w-[28%]">Book</th>
                <th class="p-3 text-center w-[12%]">Owner</th>
                <th class="p-3 text-center w-[10%]">Year</th>
                <th class="p-3 text-center w-[12%]">TMDb</th>
                <th class="p-3 text-center w-[12%]">IMDb</th>
                <th class="p-3 text-center w-[14%]">Status</th>
                <th class="p-3 text-right w-[12%]">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($books as $book)
                @php
                    $isOwner = auth()->id() === $book->user_id;
                    $bookUrl = url('/u/' . $book->user->username . '/books/' . $book->slug);
                @endphp

                <tr wire:key="book-table-{{ $book->id }}" class="border-t hover:bg-gray-50">
                    <td class="p-3">
                        @if ($isOwner)
                            <input type="checkbox" value="{{ $book->id }}" wire:model.live="selected">
                        @endif
                    </td>

                    <td class="p-3 font-medium" title="{{ $book->title }}">
                        <div class="flex items-center gap-3">
                            @if ($book->poster_path)
                                <img src="{{ asset('storage/' . $book->poster_path) }}"
                                    class="h-10 w-10 rounded object-cover border" alt="{{ $book->title }}">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded border bg-gray-50">
                                    🎬
                                </div>
                            @endif

                            <span class="truncate max-w-[220px]">
                                {{ $book->title }}
                            </span>
                        </div>
                    </td>

                    <td class="p-3 text-center">
                        <x-owner-badge :userid="$book->user_id" :username="$book->user->username" :view="$view" />
                    </td>

                    <td class="p-3 text-center">
                        {{ $book->year ?: '—' }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $book->tmdb_id ?: '—' }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $book->imdb_id ?: '—' }}
                    </td>

                    <td class="p-3">
                        <div class="flex items-center justify-center gap-3">
                            <x-status-badge :visibility="$book->visibility" :shared="($book->shares_count ?? 0) > 0" :view="$view" />

                            <x-card-footer-meta :visibility="$book->visibility" :assetId="$book->id" :isOwner="$isOwner" :likeCount="$book->likes_count ?? 0"
                                :likedByUser="$book->isLikedBy(auth()->user())" :shareCount="$book->shares_count" :assetUrl="$bookUrl" />
                        </div>
                    </td>

                    <td class="p-3">
                        <div class="flex justify-end items-center gap-3">

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

                                <button title="Move to Trash" wire:click="confirmDelete({{ $book->id }})">
                                    🗑️
                                </button>

                                <div class="relative">
                                    <button type="button"
                                        @click.stop="activeMenu = activeMenu === 'book-{{ $book->id }}' ? null : 'book-{{ $book->id }}'"
                                        class="rounded p-1 hover:bg-gray-100" title="More actions">
                                        ⋮
                                    </button>

                                    <div x-show="activeMenu === 'book-{{ $book->id }}'"
                                        @click.outside="activeMenu = null" x-transition
                                        class="absolute right-0 z-[9999] mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">

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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-6 text-center">
                        <x-empty-state icon="🎬" title="No books found"
                            message="Try changing your search/filter/access mode or create a new book." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
