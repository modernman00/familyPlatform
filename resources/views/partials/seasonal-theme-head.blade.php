@php
    $seasonalTheme = \App\services\SeasonalThemeService::current();
@endphp
@if(!empty($seasonalTheme['is_active']))
<link rel="preload" href="/public/css/seasonal-themes.css?v={{ assetVersion('css/seasonal-themes.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="/public/css/seasonal-themes.css?v={{ assetVersion('css/seasonal-themes.css') }}"></noscript>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
  (function () {
    var theme = @json($seasonalTheme);
    window.__SEASONAL_THEME__ = theme;
    var root = document.documentElement;
    root.setAttribute('data-seasonal-theme', theme.mode || 'standard');
    try {
      var optOut = localStorage.getItem('familyPlatform_seasonal_opt_out') === '1';
      var fxOff = localStorage.getItem('familyPlatform_seasonal_fx') === 'off';
      var dismissed = localStorage.getItem('familyPlatform_seasonal_dismissed_' + (theme.mode || 'standard') + '_' + new Date().getFullYear()) === '1';
      root.setAttribute('data-seasonal-opt-out', optOut ? '1' : '0');
      root.setAttribute('data-seasonal-fx', fxOff ? 'off' : 'on');
      root.setAttribute('data-seasonal-dismissed', dismissed ? '1' : '0');
    } catch (e) {}
  })();
</script>
@endif
