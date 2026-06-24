@if ($workspaces->isEmpty())
    <x-empty-state icon="🎬" title="No workspaces found"
        message="Try changing your search/filter/access mode or create a new workspace." />
@else
<div class="columns-1 md:columns-2 xl:columns-3 gap-4 space-y-4">
    @forelse($workspaces as $workspace)
        <div wire:key="workspace-masonry-{{ $workspace->id }}"
            class="break-inside-avoid mb-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <x-workspace-card :workspace="$workspace" :selected="$selected" />
        </div>
    @empty
        <div>No workspaces found.</div>
    @endforelse
</div>
@endif
