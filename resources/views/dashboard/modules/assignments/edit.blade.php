<x-dashboard-layout>
    <x-slot:title>Edit Assignment</x-slot:title>

<x-assignment-form 
    method="PATCH" 
    :$module
    :$job_listings
    :$users
    :$assignment
/>

</x-dashboard-layout>
