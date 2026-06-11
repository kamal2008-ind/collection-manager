@if ($showDrawer)

    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeDrawer"></div>

    {{-- Drawer --}}
    <div class="fixed top-0 right-0 h-screen w-[500px] bg-white shadow-xl z-50 flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between p-6 border-b shrink-0">

            <h2 class="text-xl font-semibold">
                {{ $drawerMode === 'create' ? 'Create Workspace' : 'Edit Workspace' }}
            </h2>

            <button type="button" wire:click="closeDrawer">
                ✕
            </button>

        </div>

        {{-- Form --}}
        <form wire:submit="save" class="flex flex-col flex-1 min-h-0">

            {{-- Scrollable Content --}}
            <div class="flex-1 overflow-y-auto p-6">

                {{-- Workspace Name --}}
                <div class="mb-4">

                    <label class="block mb-1 font-medium">
                        Workspace Name *
                    </label>

                    <input type="text" wire:model="name" class="w-full rounded-lg border border-gray-400 px-3 py-2 text-base focus:border-blue-600 focus:ring-blue-600">

                    @error('name')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Description --}}
                <div class="mb-4">

                    <label class="block mb-1 font-medium">
                        Description
                    </label>

                    <textarea wire:model="description" rows="4" class="w-full rounded-lg border border-gray-400 px-3 py-2 text-base focus:border-blue-600 focus:ring-blue-600"></textarea>

                </div>

                {{-- Image Preview --}}
                @if ($image)
                    <div class="mb-4">

                        <label class="block mb-2 font-medium">
                            New Image Preview
                        </label>

                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-48 object-cover rounded-lg border">

                    </div>
                @elseif ($currentImage)
                    <div class="mb-4">

                        <div class="flex items-center justify-between mb-2">

                            <label class="font-medium">
                                Current Image
                            </label>

                            <button type="button" wire:click="removeCurrentImage" class="text-red-500">
                                🗑️
                            </button>

                        </div>

                        <img src="{{ asset('storage/' . $currentImage) }}"
                            class="w-full h-48 object-cover rounded-lg border">

                    </div>
                @endif

                {{-- Upload Image --}}
                <div class="mb-4">

                    <label class="block mb-1 font-medium">
                        Image
                    </label>

                    <input type="file" wire:model="image" class="w-full rounded-lg border px-3 py-2">

                    @error('image')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Visibility --}}
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

            {{-- Footer --}}
            <div class="border-t py-8 bg-white shrink-0">

                <div class="flex justify-end gap-4">

                    <x-ui.button variant="secondary" wire:click="closeDrawer">
                        Cancel
                    </x-ui.button>

                    <x-ui.button type="submit">
                        {{ $drawerMode === 'create' ? 'Create Workspace' : 'Update Workspace' }}
                    </x-ui.button>
                </div>

            </div>

        </form>

    </div>

@endif
