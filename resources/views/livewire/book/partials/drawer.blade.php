@if ($showDrawer)

    <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeDrawer"></div>

    <div class="fixed top-0 right-0 h-screen w-[550px] bg-white shadow-xl z-[9999] flex flex-col">

        <div class="flex items-center justify-between p-6 border-b shrink-0">

            <h2 class="text-xl font-semibold">
                {{ $drawerMode === 'create' ? 'Create Book' : 'Edit Book' }}
            </h2>

            <button type="button" wire:click="closeDrawer">
                ✕
            </button>

        </div>

        <form wire:submit="save" class="flex flex-col flex-1 min-h-0">

            <div class="flex-1 overflow-y-auto p-6">

                <div class="mb-4">
                    <label class="block mb-1 font-medium">
                        Book Title *
                    </label>

                    <input
                        type="text"
                        wire:model="title"
                        class="w-full rounded-lg border border-gray-400 px-3 py-2 text-base focus:border-blue-600 focus:ring-blue-600">

                    @error('title')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">
                        Release Year
                    </label>

                    <input
                        type="number"
                        wire:model="year"
                        min="1800"
                        max="2100"
                        class="w-full rounded-lg border border-gray-400 px-3 py-2 text-base focus:border-blue-600 focus:ring-blue-600">

                    @error('year')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">
                        Description
                    </label>

                    <textarea
                        wire:model="description"
                        rows="4"
                        class="w-full rounded-lg border border-gray-400 px-3 py-2 text-base focus:border-blue-600 focus:ring-blue-600"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">

                    <div>
                        <label class="block mb-1 font-medium">
                            Author
                        </label>

                        <input
                            type="text"
                            wire:model="author"
                            class="w-full rounded-lg border border-gray-400 px-3 py-2 text-base focus:border-blue-600 focus:ring-blue-600">

                        @error('author')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">
                            Publisher
                        </label>

                        <input
                            type="text"
                            wire:model="publisher"
                            placeholder=""
                            class="w-full rounded-lg border border-gray-400 px-3 py-2 text-base focus:border-blue-600 focus:ring-blue-600">

                        @error('publisher')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                @if ($image)
                    <div class="mb-4">
                        <label class="block mb-2 font-medium">
                            New cover preview
                        </label>

                        <img src="{{ $image->temporaryUrl() }}"
                            class="w-full h-64 object-cover rounded-lg border">
                    </div>
                @elseif ($currentCover)
                    <div class="mb-4">

                        <div class="flex items-center justify-between mb-2">
                            <label class="font-medium">
                                Current cover
                            </label>

                            <button
                                type="button"
                                wire:click="removeCurrentCover"
                                class="text-red-500">
                                🗑️
                            </button>
                        </div>

                        <img
                            src="{{ asset('storage/' . $currentCover) }}"
                            class="w-full h-64 object-cover rounded-lg border">
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block mb-1 font-medium">
                        Cover
                    </label>

                    <input
                        type="file"
                        wire:model="image"
                        class="w-full rounded-lg border px-3 py-2">

                    @error('image')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium">
                        Visibility
                    </label>

                    <div class="flex gap-4">
                        <label>
                            <input type="radio" wire:model="visibility" value="private">
                            Private
                        </label>

                        <label>
                            <input type="radio" wire:model="visibility" value="public">
                            Public
                        </label>
                    </div>
                </div>

            </div>

            <div class="border-t py-8 bg-white shrink-0">

                <div class="flex justify-end gap-4">

                    <x-ui.button variant="secondary" wire:click="closeDrawer">
                        Cancel
                    </x-ui.button>

                    <x-ui.button type="submit">
                        {{ $drawerMode === 'create' ? 'Create Book' : 'Update Book' }}
                    </x-ui.button>

                </div>

            </div>

        </form>

    </div>

@endif
