@if ($showDeleteModal)
    <div class="fixed inset-0 bg-black/40 z-[9999]">

        <div class="flex items-center justify-center min-h-screen">

            <div class="bg-white rounded-xl p-6 w-[400px]">

                <h3 class="font-semibold text-lg">
                    Delete Workspace
                </h3>

                <p class="mt-2 text-gray-600">
                    Are you sure you want to move
                    <strong>"{{ $deleteWorkspaceName }}"</strong> to trash??
                </p>

                <p class="mt-2 text-sm text-gray-500">
                    This workspace will be moved to trash and can be restored later.
                </p>

                <div class="flex justify-end gap-2 mt-6">

                    <x-ui.button variant="secondary" wire:click="$set('showDeleteModal', false)">
                        Cancel
                    </x-ui.button>

                    <x-ui.button variant="danger" wire:click="deleteWorkspace">
                        Move to Trash
                    </x-ui.button>

                </div>

            </div>

        </div>

    </div>
@endif
