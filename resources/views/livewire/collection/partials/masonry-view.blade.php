<div class="columns-1 md:columns-2 xl:columns-3 gap-4 space-y-4">
    @forelse($collections as $collection)
        <div wire:key="collection-masonry-{{ $collection->id }}"
            class="break-inside-avoid mb-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <x-collection-card :collection="$collection" :selected="$selected" />
        </div>
    @empty
        <div>No collections found.</div>
    @endforelse
</div>
