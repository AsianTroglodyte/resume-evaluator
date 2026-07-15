@php
    use App\Enums\JobListingSource;
    use App\Enums\ModuleJobListingScope;
    use App\Enums\AssigneeScope;
@endphp
<x-dashboard-layout>
    <x-slot:title>Create Assignment</x-slot:title>

<x-assignment-form 
    method="PATCH" 
    :$module
    :$job_listings
    :$users
    :$assignment
/>

</x-dashboard-layout>
