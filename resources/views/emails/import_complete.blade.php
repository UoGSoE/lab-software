<x-mail::message>
# Import of software complete

The import of software has been completed.

@if ($errors->count() > 0)
## The following errors were encountered:

@foreach ($errors as $error)
- {{ $error }}
@endforeach
@endif

<x-mail::button :url="route('home')">
Visit the Lab Software App
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
