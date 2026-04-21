<x-dashboard-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    {{-- 'title' => $evaluation['name'],
    'evaluation' => $evaluation['ats_friendliness'],
    'keyword_match' => $evaluation['keyword_match'],
    'groups' --}}
    <section>
        <h1 class="text-2xl font-semibold">{{ $title }}</h1>
        <ul>
            <li>{{ $title }}</li>
            <li>{{ $evaluation }}</li>
            <li>{{ $keyword_match }}</li>
            @foreach ($groups as $group)
                <li>{{ $group["name"] }}</li>
                
            @endforeach
        </ul>
    </section>
</x-dashboard-layout>