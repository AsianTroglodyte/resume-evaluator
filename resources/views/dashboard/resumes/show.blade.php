<x-dashboard-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <section>
        <h1 class="text-2xl font-semibold">{{ $title }}</h1>
        <ul>
            <li>{{ $title }}</li>
            <li>{{ $evaluation }}</li>
            <li>{{ $keyword_match }}</li>
            @foreach ($modules as $module)
                <li>{{ $module['name'] }}</li>
            @endforeach
        </ul>
    </section>
</x-dashboard-layout>
