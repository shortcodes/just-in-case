@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ config('app.url') }}/logo.png" class="logo" alt="{{ config('app.name') }}" style="height: 75px; width: auto; max-height: 75px;">
</a>
</td>
</tr>
