<div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4">
    @forelse($collections as $collection)
        <div wire:key="collection-card-{{ $collection->id }}"
            class="shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <x-collection-card :collection="$collection" :selected="$selected" />
        </div>
    @empty
        <div><x-empty-state icon="🎬" title="No collections found" message="Try changing your search/filter/access mode or create a new collection."/></div>
    @endforelse
</div>
