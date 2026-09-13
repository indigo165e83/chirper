<x-layout>
    <x-slot:title>
        Memos
    </x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Memos</h1>
        <a href="{{ route('memos.create') }}" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Create Memo</a>
    </div>
    @forelse ($memos as $memo)
        <x-memo :memo="$memo" />
    @empty
        <p>No memos found.</p>
    @endforelse
</x-layout>