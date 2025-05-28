<x-mail::message>
# Lab Software System closing soon

The system is closing on {{ $closingDate }}.  Please - if you have not already done so - log in to the system to indicate the software you will be using for teaching this year.

<x-mail::button :url="route('home')">
Log in to the system
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
