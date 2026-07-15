<x-dashboard-layout>
    <x-slot:title>Create Assignment</x-slot:title>

    <x-assignment-form 
        method="POST" 
        :$module
        :$job_listings
        :$users
        />
</x-dashboard-layout>
