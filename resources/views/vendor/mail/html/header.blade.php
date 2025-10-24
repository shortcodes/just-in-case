@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ config('app.url') }}/logo.svg" class="logo" alt="{{ config('app.name') }}" style="height: 75px; width: auto; max-height: 75px;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
