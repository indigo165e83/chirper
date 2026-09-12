<x-layout>
    <x-slot:title>
        Memos
    </x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Memos</h1>
        @forelse ($memos as $memo)
            <div class="card w-full bg-base-100 shadow-xl mb-4">
                <div class="card-body">
                    <h2 class="card-title">{{ $memo->title }}</h2>
                    <p>{{ $memo->body }}</p>
                    <p>{{ $memo->is_draft ? 'Draft' : 'Published' }}</p>
                </div>
            </div>
        @empty
            <p>No memos found.</p>
        @endforelse
    </div>
</x-layout>