<div x-data x-show="$wire.shareDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeShareDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold">Share Book</h2>

                @if ($sharingBook)
                    <p class="mt-1 text-sm text-gray-500">{{ $sharingBook->title }}</p>
                @endif
            </div>

            <button type="button" wire:click="closeShareDrawer"
                class="text-2xl text-gray-500 hover:text-gray-800">×</button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            <div>
                <label class="mb-2 block text-base font-medium text-gray-900">
                    Search user by name, username or email
                </label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.400ms="shareSearch" placeholder="Start typing..."
                        class="w-full rounded-lg border border-gray-400 px-4 py-3 text-base focus:border-blue-600 focus:ring-blue-600">
                    @if ($shareSearch)
                        <button type="button" wire:click="$set('shareSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                            title="Clear search">
                            ✕
                        </button>
                    @endif
                </div>
                @if (!empty($shareSearchResults))
                    <div class="mt-4">
                        <h3 class="mb-3 text-base font-semibold text-gray-900">Available Users</h3>

                        <div class="overflow-hidden rounded-lg border bg-white">
                            @foreach ($shareSearchResults as $user)
                                <div class="flex items-center justify-between border-b px-4 py-3 last:border-b-0">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $user['username'] }} ·
                                            {{ $user['email'] }}</div>
                                    </div>

                                    <x-ui.button variant="primary" wire:click="shareWithUser({{ $user['id'] }})">
                                        Share
                                    </x-ui.button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif(strlen($shareSearch) >= 2)
                    <p class="mt-2 text-sm text-gray-500">No users found.</p>
                @endif
            </div>

            <div>
                <h3 class="mb-3 text-base font-semibold text-gray-900">Already Shared</h3>

                @if (!empty($sharedUsers))
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($sharedUsers as $user)
                            <div class="flex items-center justify-between border-b px-4 py-3 last:border-b-0">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $user['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $user['username'] }} · {{ $user['email'] }}
                                    </div>
                                </div>

                                <button type="button" wire:click="removeSharedUser({{ $user['id'] }})"
                                    wire:confirm="Remove this user's access?"
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="rounded-lg border border-dashed p-4 text-gray-500">
                        This private book is not shared with anyone yet.
                    </p>
                @endif
            </div>

            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">
                Shared users can only view this book. They cannot edit, trash, attach, detach, share, or change
                settings.
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeShareDrawer">Cancel</x-ui.button>
        </div>
    </div>
</div>
