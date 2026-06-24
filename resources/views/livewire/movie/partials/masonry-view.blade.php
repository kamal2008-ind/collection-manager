@if ($movies->isEmpty())
    <x-empty-state icon="🎬" title="No movies found"
        message="Try changing your search/filter/access mode or create a new movie." />
@else
    <div class="columns-1 md:columns-2 xl:columns-3 gap-4 space-y-4">
        @forelse($movies as $movie)
            <div wire:key="movie-masonry-{{ $movie->id }}"
                class="break-inside-avoid mb-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <x-movie-card :movie="$movie" :selected="$selected" />
            </div>
        @empty
            <div>No movies found.</div>
        @endforelse
    </div>
@endif
