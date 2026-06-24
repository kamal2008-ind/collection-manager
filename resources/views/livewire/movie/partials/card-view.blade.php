<div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4">
    @forelse($movies as $movie)
        <div wire:key="movie-card-{{ $movie->id }}"
            class="shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <x-movie-card :movie="$movie" :selected="$selected" />
        </div>
    @empty
        <div><x-empty-state icon="🎬" title="No movies found" message="Try changing your search/filter/access mode or create a new movie."/></div>
    @endforelse
</div>
