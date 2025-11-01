<table class="disclaimer-footer" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="disclaimer-content">
<table align="center" cellpadding="0" cellspacing="0" style="max-width: 1200px; margin: 0 auto; width: 100%;">
<tr>
<td class="disclaimer-column" width="50%" style="vertical-align: top; padding-right: 20px;">
<h3 class="disclaimer-heading">{{ config('app.name') }}</h3>
<p class="disclaimer-text">{{ __('This message was automatically sent by :appName when the account owner stopped resetting their timer.', ['appName' => config('app.name')]) }}</p>
<p class="disclaimer-text disclaimer-warning"><strong>{{ __('Legal Disclaimer') }}:</strong> {{ __('This is NOT a legal testament or official document.') }}</p>
</td>
<td class="disclaimer-column" width="50%" style="vertical-align: top; padding-left: 20px;">
<h3 class="disclaimer-heading">{{ __('Resources') }}</h3>
<p class="disclaimer-text"><a href="{{ $url ?? config('app.url') }}" class="disclaimer-link">{{ __('Visit Website') }}</a></p>
<p class="disclaimer-text"><a href="{{ $url ?? config('app.url') }}/about" class="disclaimer-link">{{ __('About Us') }}</a></p>
<p class="disclaimer-text"><a href="{{ $url ?? config('app.url') }}/privacy" class="disclaimer-link">{{ __('Privacy Policy') }}</a></p>
<p class="disclaimer-text"><a href="{{ $url ?? config('app.url') }}/terms" class="disclaimer-link">{{ __('Terms of Service') }}</a></p>
</td>
</tr>
</table>
</td>
</tr>
</table>
