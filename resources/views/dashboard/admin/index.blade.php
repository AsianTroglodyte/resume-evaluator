@php
$admins = $users->filter(fn ($user) => $user->global_role === "admin");
$non_admin_users = $users->filter(fn ($user) => $user->global_role === "user");
@endphp

<x-dashboard-layout>

   <table class="table">
        <thead>
        <tr>
            <td>first name</td>
            <td>last name</td>
            <td>email</td>
            <td>global role</td>
            <td>email verified</td>
            <td>created at</td>
            <td>updated at</td>
        </tr>
        </thead>
        <tbody>
            @foreach ($non_admin_users as $non_admin_user)
            <tr>
                <td>{{ $non_admin_user->first_name}}</td>
                <td>{{ $non_admin_user->last_name}}</td>
                <td>{{ $non_admin_user->email}}</td>
                <td>{{ $non_admin_user->global_role}}</td>
                <td>{{ $non_admin_user->email_verified_at}}</td>
                <td>{{ $non_admin_user->created_at}}</td>
                <td>{{ $non_admin_user->updated_at}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
            
</x-dashboard-layout>