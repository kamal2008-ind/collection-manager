<div x-data x-show="$wire.attachToDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeAttachToDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-blue-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-blue-700">
                    {{ $isBulkAttachMode ? 'Bulk Attach To Workspace' : 'Attach To Workspace' }}
                </h2>
                @if ($attachToCollectionName)
                    <p class="mt-1 text-sm text-blue-600">
                        {{ $attachToCollectionName }}
                    </p>
                @endif
            </div>

            <button type="button" wire:click="closeAttachToDrawer" class="text-2xl text-gray-500 hover:text-gray-800">
                ×
            </button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            @php
                $attachedWorkspaces = collect($workspaceOptions)->whereIn('id', $attachedWorkspaceIds);

                $availableWorkspaces = collect($workspaceOptions)
                    ->whereNotIn('id', $attachedWorkspaceIds)
                    ->filter(function ($workspace) use ($workspaceSearch) {
                        if (blank($workspaceSearch)) {
                            return true;
                        }

                        return str_contains(strtolower($workspace['name']), strtolower($workspaceSearch));
                    });

                $visibleWorkspaces = $availableWorkspaces->take($drawerPerPage);

                $hasMoreWorkspaces = $availableWorkspaces->count() > $drawerPerPage;
            @endphp
            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Available Workspaces
                </h3>
                <div class="relative mb-3">
                    <input type="text" wire:model.live.debounce.300ms="workspaceSearch"
                        placeholder="Search available workspaces..."
                        class="w-full rounded-lg border-gray-300 pr-10 focus:border-blue-600 focus:ring-blue-600" />

                    @if ($workspaceSearch)
                        <button type="button" wire:click="$set('workspaceSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                            title="Clear search">
                            ✕
                        </button>
                    @endif
                </div>
                @if ($availableWorkspaces->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleWorkspaces as $workspace)
                            <label wire:key="available-workspace-{{ $workspace['id'] }}"
                                class="flex cursor-pointer items-center gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-gray-50">
                                <input type="checkbox" value="{{ $workspace['id'] }}"
                                    wire:model.live="selectedWorkspaceIds" class="rounded border-gray-400">

                                <span class="font-medium">
                                    {{ $workspace['name'] }}
                                </span>
                            </label>
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
                    @endif
                    @if (!$hasMoreWorkspaces && $visibleWorkspaces->isNotEmpty())
                        <div class="py-4 text-center text-sm text-gray-400">
                            ✓ No more workspaces
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No available workspaces to attach.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-blue-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeAttachToDrawer">
                Cancel
            </x-ui.button>

            @php
                $disableUpdateAttachment =
                    empty($workspaceOptions) || (!$isBulkAttachMode && $availableWorkspaces->isEmpty());
            @endphp

            <x-ui.button variant="primary" wire:click="attachToSelectedWorkspaces" :disabled="$disableUpdateAttachment">
                Update Attachment
                @if (count($selectedWorkspaceIds))
                    ({{ count($selectedWorkspaceIds) }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
