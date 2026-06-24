<div>
    @if ($isPrivateBlocked)
        <div class="min-h-[70vh] flex items-center justify-center p-6">
            <div class="max-w-md rounded-2xl border bg-white p-8 text-center shadow-sm">
                <div class="text-5xl mb-4">🔒</div>

                <h1 class="text-2xl font-bold">
                    This collection is private
                </h1>

                <p class="mt-3 text-gray-600">
                    The owner has not made this collection public, so it cannot be viewed from this link.
                </p>

                @guest
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('login') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">
                            Create Account
                        </a>
                    </div>
                @endguest
            </div>
        </div>
    @else
        <div class="p-6 space-y-6">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                @if ($collection->image)
                    <img src="{{ asset('storage/' . $collection->image) }}"
                        class="mb-6 h-64 w-full rounded-xl object-cover">
                @endif

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold">
                            {{ $collection->name }}
                        </h1>

                        <p class="mt-2 text-gray-600">
                            Created by {{ $collection->user->name }}
                        </p>
                    </div>

                    <div>
                        @php
                            $isOwner = auth()->id() === $collection->user_id;
                            $isSharedWithMe = auth()->check() && !$isOwner && $collection->isSharedWith(auth()->user());
                        @endphp
                        @if ($collection->visibility === 'public')
                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">
                                Public
                            </span>
                        @elseif ($isSharedWithMe)
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-700">
                                Shared with me
                            </span>
                        @else
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">
                                Private
                            </span>
                        @endif
                    </div>
                </div>

                @if ($collection->description)
                    <p class="mt-6 text-gray-700">
                        {{ $collection->description }}
                    </p>
                @endif

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg border p-4">
                        Workspaces
                        <div class="text-2xl font-bold">
                            {{ $collection->workspaces_count ?? 0 }}
                        </div>
                    </div>

                    <div class="rounded-lg border p-4">
                        Movies
                        <div class="text-2xl font-bold">
                            {{ $collection->movies_count ?? 0 }}
                        </div>
                    </div>

                    <div class="rounded-lg border p-4">
                        Books
                        <div class="text-2xl font-bold">0</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
