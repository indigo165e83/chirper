<x-layout>
    <x-slot:title>
        Memos
    </x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Memos</h1>
        <a href="{{ route('memos.create') }}" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Create Memo</a>
    </div>
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
</x-layout>