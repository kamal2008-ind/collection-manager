@props([
    'title' => 'No records found',
    'message' => 'Try changing your filters or create a new item.',
    'icon' => '📭',
])

<div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
    <div class="mb-3 text-5xl">
        {{ $icon }}
    </div>

    <h3 class="text-lg font-semibold text-gray-900">
        {{ $title }}
    </h3>

    <p class="mt-2 text-sm text-gray-500">
        {{ $message }}
    </p>
</div>
