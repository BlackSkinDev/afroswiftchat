@component('mail::message')
# Hello {{$user}}

Welcome to Afroswift chat,
Have a wonderful chit chat with your friends.

@component('mail::button', ['url' => '/home'])
Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
