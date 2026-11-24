@php
    $seasonalTheme = \App\services\SeasonalThemeService::current();
    $mode = (string) ($seasonalTheme['mode'] ?? 'standard');
@endphp
@if(!empty($seasonalTheme['is_active']))
<canvas id="seasonalAmbientCanvas" aria-hidden="true"></canvas>

<div class="seasonal-ribbon-wrap" id="seasonalRibbonWrap" role="region" aria-label="{{ $seasonalTheme['label'] }} seasonal theme">
  <div class="seasonal-ribbon">
    <div class="seasonal-fairy-lights" aria-hidden="true">
      <span class="seasonal-fairy-dot seasonal-fairy-dot--gold"></span>
      <span class="seasonal-fairy-dot seasonal-fairy-dot--crimson"></span>
      <span class="seasonal-fairy-dot seasonal-fairy-dot--pine"></span>
      <span class="seasonal-fairy-dot seasonal-fairy-dot--gold"></span>
      <span class="seasonal-fairy-dot seasonal-fairy-dot--crimson"></span>
      <span class="seasonal-fairy-dot seasonal-fairy-dot--pine"></span>
      <span class="seasonal-fairy-dot seasonal-fairy-dot--gold"></span>
    </div>
    <div class="seasonal-ribbon__identity">
      <span class="seasonal-ribbon__icon" aria-hidden="true">
        @if($mode === \App\services\SeasonalThemeService::MODE_CHRISTMAS)
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L7.5 8.5h3L6 14h3.5L5 20h14l-4.5-6H18l-4.5-5.5h3L12 2z" fill="currentColor" fill-opacity="0.2"/><path d="M12 20v2"/></svg>
        @elseif($mode === \App\services\SeasonalThemeService::MODE_VALENTINES || $mode === \App\services\SeasonalThemeService::MODE_MOTHERS_DAY)
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" fill-opacity="0.25" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        @elseif($mode === \App\services\SeasonalThemeService::MODE_EID_FITR || $mode === \App\services\SeasonalThemeService::MODE_EID_ADHA)
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        @elseif($mode === \App\services\SeasonalThemeService::MODE_DIWALI)
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3c1.5 2.5 2.5 4.2 2.5 6a2.5 2.5 0 0 1-5 0c0-1.8 1-3.5 2.5-6Z" fill="currentColor" fill-opacity="0.3"/><path d="M4 14h16c0 3.5-3.5 6-8 6s-8-2.5-8-6Z" fill="currentColor" fill-opacity="0.18"/></svg>
        @else
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.1 6.2L20.5 10l-5.3 3.9L17 20.5 12 16.8 7 20.5l1.8-6.6L3.5 10l6.4-1.8L12 2z" fill="currentColor" fill-opacity="0.22"/></svg>
        @endif
      </span>
      <div class="seasonal-ribbon__copy">
        <span class="seasonal-ribbon__title">{{ $seasonalTheme['badge_text'] }}</span>
        <span class="seasonal-ribbon__sub">{{ $seasonalTheme['sub_greeting'] }}</span>
      </div>
    </div>
    <div class="seasonal-ribbon__actions">
      <button type="button" class="seasonal-ribbon__btn" id="seasonalFxToggleBtn" aria-pressed="false" title="Pause or resume festive animations">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/></svg>
        <span id="seasonalFxToggleLabel">Pause FX</span>
      </button>
      <button type="button" class="seasonal-ribbon__btn" id="seasonalThemeOptOutBtn" aria-pressed="false" title="Switch back to standard FamilyPlatform colours">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
        <span>Standard Look</span>
      </button>
      <button type="button" class="seasonal-ribbon__btn seasonal-ribbon__btn--icon" id="seasonalRibbonDismissBtn" aria-label="Dismiss festive banner" title="Dismiss festive banner">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
  </div>
</div>

<div class="seasonal-restore-wrap" id="seasonalRestoreWrap" hidden>
  <button type="button" class="seasonal-restore-pill" id="seasonalRestoreBtn" title="Restore {{ $seasonalTheme['label'] }} look">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l2.1 6.2L20.5 10l-5.3 3.9L17 20.5 12 16.8 7 20.5l1.8-6.6L3.5 10l6.4-1.8L12 2z"/></svg>
    <span>Festive Look</span>
  </button>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/seasonal-theme.js?v={{ assetVersion('js/seasonal-theme.js') }}" defer></script>
@endif
