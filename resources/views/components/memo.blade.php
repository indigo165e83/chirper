@props(['memo'])

<div class="card bg-base-100 shadow">
    <div class="card-body">
        <div>
            <div>
                <div class="flex justify-end w-full">
                    @can('update', $memo)
                        <!-- Edit/Delete buttons -->
                        <div class="flex gap-1">
                            <a href="{{ route('memos.edit', $memo) }}" class="btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('memos.destroy', $memo) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    onclick="return confirm('Are you sure you want to delete this memo?')"
                                    class="btn btn-sm btn-error">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>
                <h2 class="card-title">
                    {{ $memo->title }}
                </h2>
                <p class="mt-1">{{ $memo->body }}</p>
                <p class="mt-1">{{ $memo->is_draft ? '(Draft)' : '' }}</p>
            </div>
        </div>
    </div>
</div>