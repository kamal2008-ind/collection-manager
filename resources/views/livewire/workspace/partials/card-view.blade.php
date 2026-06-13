<div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4">
    @forelse($workspaces as $workspace)
        <div wire:key="workspace-card-{{ $workspace->id }}"
            class="shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <x-workspace-card :workspace="$workspace" :selected="$selected" />
        </div>
    @empty
        <div>No workspaces found.</div>
    @endforelse
</div>
