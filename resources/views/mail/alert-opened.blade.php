<x-mail::message>
# {{ $alert->title }}

**ДГУ:** {{ $dgu->serial_number }}@if($dgu->name) ({{ $dgu->name }})@endif

**Параметр:** `{{ $alert->parameter_slug }}`

@if($alert->triggered_value)
**Значение:** {{ $alert->triggered_value }}
@endif

@if($alert->message)
{{ $alert->message }}
@endif

<x-mail::button :url="$openUrl">
Открыть тревогу
</x-mail::button>

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
