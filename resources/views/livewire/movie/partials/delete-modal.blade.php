@if ($showDeleteModal)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold">
                Move Movie to Trash?
            </h2>

            <p class="mt-3 text-gray-600">
                Are you sure you want to move
                <strong>{{ $deleteMovieTitle }}</strong>
                to trash?
            </p>

            <p class="mt-2 text-sm text-gray-500">
                The movie will be soft deleted and can be restored later.
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui.button variant="secondary" wire:click="$set('showDeleteModal', false)">
                    Cancel
                </x-ui.button>

                <x-ui.button variant="danger" wire:click="deleteMovie">
                    Move to Trash
                </x-ui.button>
            </div>
        </div>
    </div>
@endif
