<x-mail::message>

# Expired Users Command

Hi Devs,

the command found {{$expiredUsers->count()}} expired users.

here are the username(s) found in the database and purged:

@foreach($expiredUsers->get() as $user)

- {{$user->username}}

@endforeach

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
