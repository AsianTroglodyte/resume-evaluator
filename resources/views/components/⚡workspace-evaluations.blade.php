<div
    class="space-y-4"
>
    @forelse ($evaluations as $evaluation)
        <livewire:x-evaluation :$evaluation :$expandedIds> </livewire:x-evaluation>
    @empty
        <div class="rounded-box border border-base-300 bg-base-100 px-4 py-5 sm:px-6">
            <p class="text-sm text-base-content/60">No evaluation run yet. Submit the form above to see results here.</p>
        </div>
    @endforelse
</div>
