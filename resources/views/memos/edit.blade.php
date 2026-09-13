<x-layout>
    <x-slot:title>
        Update Memo
    </x-slot:title>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Update Memo</h1>
    </div>

    <form action="{{ route('memos.update', $memo) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" id="title" value="{{ old('title', $memo->title) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            @error('title') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
            <textarea name="body" id="body" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('body', $memo->body) }}</textarea>
            @error('body') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <div class="flex items-center space-x-2">
                <input type="hidden" name="is_draft" value="0">
                <input 
                    type="checkbox" 
                    name="is_draft" 
                    id="is_draft" 
                    value="1" 
                    {{ old('is_draft', $memo->is_draft) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                >
                <label for="is_draft" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Is Draft
                </label>
            </div>
        </div>
        <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Update Memo</button>
    </form>
</x-layout>