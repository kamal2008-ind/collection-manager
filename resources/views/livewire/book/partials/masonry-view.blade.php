@if ($books->isEmpty())
    <x-empty-state icon="🎬" title="No books found"
        message="Try changing your search/filter/access mode or create a new book." />
@else
    <div class="columns-1 md:columns-2 xl:columns-3 gap-4 space-y-4">
        @forelse($books as $book)
            <div wire:key="book-masonry-{{ $book->id }}"
                class="break-inside-avoid mb-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <x-book-card :book="$book" :selected="$selected" />
            </div>
        @empty
            <div>No books found.</div>
        @endforelse
    </div>
@endif
