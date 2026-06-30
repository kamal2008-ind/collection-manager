<div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4">
    @forelse($books as $book)
        <div wire:key="book-card-{{ $book->id }}"
            class="shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <x-book-card :book="$book" :selected="$selected" />
        </div>
    @empty
        <div><x-empty-state icon="🎬" title="No books found" message="Try changing your search/filter/access mode or create a new book."/></div>
    @endforelse
</div>
