
<x-dashboard-layout>
    <x-slot:title>{{ $group_name }}</x-slot:title>

    <section class="space-y-4">
    <header>
    <header class="space-y-1">
        <h2 class="text-2xl font-semibold">{{ $group_name }}</h2>
        <p class="text-sm text-base-content/70">
            Status: {{ ucfirst($status) }} · {{ $pending_assignment }} assignment pending
        </p>
    </header>
    <div class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-box border border-base-300 bg-base-100 p-4">
            <h3 class="text-lg font-semibold">Assignments</h3>
            <p class="mb-3 text-sm text-base-content/70">Group tasks and submission targets.</p>
            <div class="space-y-3">
            @forelse ($assignments as $assignment)

                <div class="rounded-box border border-base-300 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <h4 class="font-medium">{{ $assignment['title'] }}</h4>
                        <span class="badge {{ $assignment['status'] === 'active' ? 'badge-primary' : 'badge-success' }}">
                            {{ ucfirst($assignment['status']) }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-base-content/70">Due: {{ $assignment['due_date'] }}</p>
                </div>
            @empty
                <p class="text-sm text-base-content/70">No assignments for this group yet.</p>
            @endforelse
            </div>
        </article>
        <article class="rounded-box border border-base-300 bg-base-100 p-4">
            <h3 class="text-lg font-semibold">Job Descriptions</h3>
            <p class="mb-3 text-sm text-base-content/70">Relevant postings for this group.</p>
            <div class="space-y-3">
            @forelse ($job_listings as $listing)
                <div class="rounded-box border border-base-300 p-3">
                    <h4 class="font-medium">{{ $listing['name'] }}</h4>
                    <p class="mt-2 text-sm text-base-content/80">{{ $listing['description'] }}</p>
                </div>
            @empty
                <p class="text-sm text-base-content/70">No job listings available for this group.</p>
            @endforelse
            </div>
        </article>
    </div>
    </section>
</x-dashboard-layout>