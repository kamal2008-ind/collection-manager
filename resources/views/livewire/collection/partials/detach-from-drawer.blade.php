<div x-data x-show="$wire.detachFromDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeDetachFromDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-orange-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-orange-700">
                    Detach Collection From Workspace
                </h2>

                @if ($detachFromCollectionName)
                    <p class="mt-1 text-sm text-orange-600">
                        {{ $detachFromCollectionName }}
                    </p>
                @endif
            </div>

            <button type="button" wire:click="closeDetachFromDrawer" class="text-2xl text-gray-500 hover:text-gray-800">
                ×
            </button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            @php
                $attachedWorkspaces = collect($detachWorkspaceOptions)->filter(function ($workspace) use (
                    $detachWorkspaceSearch,
                ) {
                    if (blank($detachWorkspaceSearch)) {
                        return true;
                    }

                    return str_contains(strtolower($workspace['name']), strtolower($detachWorkspaceSearch));
                });

                $visibleWorkspaces = $attachedWorkspaces->take($drawerPerPage);
                $hasMoreWorkspaces = $attachedWorkspaces->count() > $drawerPerPage;
            @endphp

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Attached Workspaces
                </h3>

                <div class="relative mb-3">
                    <input type="text" wire:model.live.debounce.300ms="detachWorkspaceSearch"
                        placeholder="Search attached workspaces..."
                        class="w-full rounded-lg border-gray-300 pr-10 focus:border-orange-600 focus:ring-orange-600" />

                    @if ($detachWorkspaceSearch)
                        <button type="button" wire:click="$set('detachWorkspaceSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                            title="Clear search">
                            ✕
                        </button>
                    @endif
                </div>

                @if ($attachedWorkspaces->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleWorkspaces as $workspace)
                            <div wire:key="detach-workspace-{{ $workspace['id'] }}"
                                class="flex items-center justify-between border-b px-4 py-3 last:border-b-0 hover:bg-orange-50">
                                <label class="flex cursor-pointer items-center gap-3">
                                    <input type="checkbox" value="{{ $workspace['id'] }}"
                                        wire:model.live.number="selectedDetachWorkspaceIds"
                                        class="rounded border-gray-400">

                                    <span class="font-medium">
                                        {{ $workspace['name'] }}
                                    </span>
                                </label>

                                <button type="button" wire:click="detachWorkspaceFromDrawer({{ $workspace['id'] }})"
                                    wire:confirm="Detach this collection from {{ $workspace['name'] }}? This will only remove the attachment. The collection will not be deleted."
                                    class="rounded-lg px-3 py-2 text-sm text-orange-600 hover:bg-orange-100"
                                    title="Detach">
                                    ⛓️‍💥
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @if ($hasMoreWorkspaces)
                        <div x-data x-intersect="$wire.loadMoreDrawer()" class="py-4 text-center">
                            <div wire:loading wire:target="loadMoreDrawer" class="text-sm text-gray-500">
                                Loading more...
                            </div>

                            <div wire:loading.remove wire:target="loadMoreDrawer" class="text-sm text-gray-400">
                                Scroll down to load more
                            </div>
                        </div>
                    @else
                        <div class="py-4 text-center text-sm text-gray-400">
                            No more workspaces
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No attached workspaces found.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-orange-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeDetachFromDrawer">
                Cancel
            </x-ui.button>

             @php
                $disableDetachFrom = $attachedWorkspaces->isEmpty();
            @endphp

            <x-ui.button variant="danger" wire:click="detachSelectedWorkspaces" :disabled="$disableDetachFrom"
                wire:confirm="Detach selected workspace(s) from this collection? This will only remove attachments. Records will not be deleted.">
                Detach From
                @if (count($selectedDetachWorkspaceIds))
                    ({{ count($selectedDetachWorkspaceIds) }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
