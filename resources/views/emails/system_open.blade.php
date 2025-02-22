<x-mail::message>
# Lab Software System Open

@if($softwareList->count() > 0)
## Your courses from last year

@foreach($softwareList as $courseCode => $software)
@if ($software->count() > 0)
**{{ $courseCode }}**
@foreach($software as $softwareName)
- {{ $softwareName }}
@endforeach
@endif
@endforeach

If the above is going to be the same for the next academic year, please just click the button below to short-circuit the process.

<x-mail::button :url="config('app.url')">
Looks good to me
</x-mail::button>

Otherwise, please log into the Lab Software System to indicate the software you will be using for teaching this year.

@else

Please log into the Lab Software System to indicate the software you will be using for teaching this year.

@endif

<x-mail::button :url="config('app.url')">
Lab Software System
</x-mail::button>

Thanks,
The Lab Software Team
</x-mail::message>
