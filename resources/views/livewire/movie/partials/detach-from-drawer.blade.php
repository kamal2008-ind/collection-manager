<div x-data x-show="$wire.detachFromDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeDetachFromDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-orange-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-orange-700">
                    Detach From
                </h2>

                @if ($detachFromMovieName)
                    <p class="mt-1 text-sm text-orange-600">
                        {{ $detachFromMovieName }}
                    </p>
                @endif
            </div>

            <button type="button" wire:click="closeDetachFromDrawer" class="text-2xl text-gray-500 hover:text-gray-800">
                ×
            </button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Attached Type
                </h3>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 hover:bg-gray-50
                        {{ $detachTargetType === 'workspace' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-100' : 'bg-white' }}">
                        <input
                            type="radio"
                            value="workspace"
                            wire:model.live="detachTargetType">
                        <span class="font-medium">Workspace</span>
                    </label>

                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 hover:bg-gray-50
                        {{ $detachTargetType === 'collection' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-100' : 'bg-white' }}">
                        <input
                            type="radio"
                            value="collection"
                            wire:model.live="detachTargetType">
                        <span class="font-medium">Collection</span>
                    </label>
                </div>
            </div>

            @php
                $attachedTargets = collect(
                    $detachTargetType === 'collection'
                        ? ($detachCollectionOptions ?? [])
                        : ($detachWorkspaceOptions ?? [])
                )->filter(function ($target) use ($detachSearch) {
                    if (blank($detachSearch)) {
                        return true;
                    }

                    return str_contains(strtolower($target['name']), strtolower($detachSearch));
                });

                $visibleTargets = $attachedTargets->take($drawerPerPage);
                $hasMoreTargets = $attachedTargets->count() > $drawerPerPage;

                $targetLabel = $detachTargetType === 'collection'
                    ? 'Collections'
                    : 'Workspaces';
            @endphp

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Attached {{ $targetLabel }}
                </h3>

                <div class="relative mb-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="detachSearch"
                        placeholder="Search attached {{ strtolower($targetLabel) }}..."
                        class="w-full rounded-lg border-gray-300 pr-10 focus:border-orange-600 focus:ring-orange-600" />

                    @if ($detachSearch)
                        <button
                            type="button"
                            wire:click="$set('detachSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700">
                            ✕
                        </button>
                    @endif
                </div>

                @if ($attachedTargets->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleTargets as $target)
                            <div
                                wire:key="detach-{{ $detachTargetType }}-{{ $target['id'] }}"
                                class="flex items-center justify-between border-b px-4 py-3 last:border-b-0 hover:bg-orange-50">

                                <label class="flex cursor-pointer items-center gap-3">
                                    @if ($detachTargetType === 'collection')
                                        <input
                                            type="checkbox"
                                            value="{{ $target['id'] }}"
                                            wire:model.live.number="selectedDetachCollectionIds"
                                            class="rounded border-gray-400">
                                    @else
                                        <input
                                            type="checkbox"
                                            value="{{ $target['id'] }}"
                                            wire:model.live.number="selectedDetachWorkspaceIds"
                                            class="rounded border-gray-400">
                                    @endif

                                    <span class="font-medium">
                                        {{ $target['name'] }}
                                    </span>
                                </label>

                                <button
                                    type="button"
                                    wire:click="detachTargetFromDrawer('{{ $detachTargetType }}', {{ $target['id'] }})"
                                    wire:confirm="Detach this movie? This will only remove the attachment."
                                    class="rounded-lg px-3 py-2 text-sm text-orange-600 hover:bg-orange-100">
                                    ⛓️‍💥
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No attached records found.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-orange-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeDetachFromDrawer">
                Cancel
            </x-ui.button>

            <x-ui.button
                variant="danger"
                wire:click="detachSelectedTargets"
                wire:confirm="Detach selected attachment(s)? Records will not be deleted.">
                Detach From
            </x-ui.button>
        </div>
    </div>
</div>
