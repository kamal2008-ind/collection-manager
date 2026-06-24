<div>
    @if ($isPrivateBlocked)
        <div class="min-h-[70vh] flex items-center justify-center p-6">
            <div class="max-w-md rounded-2xl border bg-white p-8 text-center shadow-sm">
                <div class="text-5xl mb-4">🔒</div>

                <h1 class="text-2xl font-bold">
                    This movie is private
                </h1>

                <p class="mt-3 text-gray-600">
                    The owner has not made this movie public, so it cannot be viewed from this link.
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
                <div class="grid gap-6 lg:grid-cols-[280px,1fr]">
                    <div class="flex justify-center lg:block">
                        @if ($movie->poster_path)
                            <img src="{{ asset('storage/' . $movie->poster_path) }}"
                                class="w-full max-w-[280px] aspect-[2/3] rounded-xl border object-cover">
                        @else
                            <div
                                class="w-full max-w-[280px] aspect-[2/3] rounded-xl border bg-gray-50 flex items-center justify-center text-6xl">
                                🎬
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-bold">
                                    {{ $movie->title }}
                                </h1>

                                <p class="mt-2 text-gray-600">
                                    Created by {{ $movie->user->name }}
                                </p>
                            </div>

                            <div>
                                @php
                                    $isOwner = auth()->id() === $movie->user_id;
                                @endphp

                                @if ($movie->visibility === 'public')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">
                                        Public
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">
                                        Private
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-lg border p-4">
                                Year
                                <div class="text-2xl font-bold">
                                    {{ $movie->year ?: '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border p-4">
                                TMDb ID
                                <div class="text-2xl font-bold">
                                    {{ $movie->tmdb_id ?: '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border p-4">
                                IMDb ID
                                <div class="text-2xl font-bold">
                                    {{ $movie->imdb_id ?: '—' }}
                                </div>
                            </div>
                        </div>

                        @if ($movie->description)
                            <div class="mt-6">
                                <h2 class="text-lg font-semibold">
                                    Description
                                </h2>

                                <p class="mt-2 leading-7 text-gray-700">
                                    {{ $movie->description }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            <span class="whitespace-nowrap" title="Attached workspaces">
                                🏢 <span
                                    class="rounded bg-purple-100 px-1 py-1 text-lg text-purple-700">{{ $movie->workspaces_count ?? 0 }}
                                    Workspaces </span>
                            </span>

                            <span class="whitespace-nowrap" title="Attached collections">
                                📁 <span
                                    class="rounded bg-yellow-100 px-1 py-1 text-lg text-yellow-700">{{ $movie->collections_count ?? 0 }}
                                    Collections </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
