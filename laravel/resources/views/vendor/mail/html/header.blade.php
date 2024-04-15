@props(['url'])
<tr>
<td class="header">
<a href="{{ config('app.frontend_url') }}" style="display: inline-block;">
@if (trim($slot) === config('app.name'))
<img src="{{ config('app.env')=='production' ? config('app.base_url').'/PWFLogo.png':'https://pwf.com.au/wp-content/uploads/2023/07/PWFlogonew-color-CMYK-1.png' }}" class="logo" alt="PWF Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
