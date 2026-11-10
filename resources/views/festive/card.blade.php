{{--
    Couture festive card — the single source of truth for every place a card is shown
    (studio live preview, design-style tiles, family archive, living envelope).
    Styles: public/css/festive-cards.css   Behaviour: public/js/festive-cards.js

    Static:  @include('festive.card', ['card' => $hydratedCard])
    Live:    @include('festive.card', ['live' => true])               — binds to Alpine `c` (see festiveStudioWizard)
             @include('festive.card', ['live' => true, 'styleExpr' => "'plaque'"]) — same content, forced style
    Options: 'tilt' => true (pointer/gyro parallax), 'thumb' => true (lighter), 'animateIn' => true
--}}
@php
    $live = (bool)($live ?? false);
    $tilt = (bool)($tilt ?? false);
    $thumb = (bool)($thumb ?? false);
    $animateIn = (bool)($animateIn ?? false);
    $styleExpr = $styleExpr ?? 'c.style';
    $card = $card ?? [];

    $styleKey = (string)($card['template_style'] ?? 'classic');
    $heroUrl = (string)($card['hero_url'] ?? '');
    $mono = (string)($card['monogram'] ?? 'F');
    $isPair = mb_strlen($mono) > 1;
    $vars = !$live ? \App\services\AtgFestiveCardService::cssVars(
        (array)($card['theme_meta'] ?? []),
        (string)($card['headline'] ?? ''),
        (string)($card['verse'] ?? '')
    ) : '';
    $staticClasses = 'fc-style-' . $styleKey . ' ' . ($heroUrl !== '' ? 'fc-has-photo' : 'fc-no-photo');
    $baseClasses = 'fc-card fc-idle' . ($thumb ? ' fc-thumb' : '') . ($animateIn ? ' fc-animate-in' : '');
@endphp
<article
    class="{{ $baseClasses }} {{ $live ? '' : $staticClasses }}"
    @if($live)
        :class="['fc-style-' + ({{ $styleExpr }}), c.photo ? 'fc-has-photo' : 'fc-no-photo']"
        :style="c.vars"
    @else
        style="{{ $vars }}"
    @endif
    @if($tilt) data-fc-tilt @endif
    aria-label="{{ $live ? 'Live card preview' : ('Greeting card: ' . ($card['headline'] ?? '')) }}"
    @if($live) :aria-label="'Greeting card: ' + c.headline" @endif
>
    <div class="fc-tilt">
        <div class="fc-face">
            <div class="fc-bg"></div>
            <div class="fc-fx" aria-hidden="true"><i></i><i></i><i></i></div>
            <div class="fc-paperwrap" aria-hidden="true"><div class="fc-paper"></div></div>
            <div class="fc-frame" aria-hidden="true"></div>
            <span class="fc-corner fc-corner--tl" aria-hidden="true"></span>
            <span class="fc-corner fc-corner--tr" aria-hidden="true"></span>
            <span class="fc-corner fc-corner--bl" aria-hidden="true"></span>
            <span class="fc-corner fc-corner--br" aria-hidden="true"></span>

            <div class="fc-content">
                <p class="fc-eyebrow fc-metal" @if($live) x-text="c.eyebrow" @endif>{{ $card['eyebrow'] ?? '' }}</p>
                <div class="fc-rule" aria-hidden="true"></div>

                <figure class="fc-hero">
                    <div class="fc-hero__frame">
                        @if($live)
                            <img class="fc-hero__img" :src="c.photo || ''" x-show="c.photo" alt="Card photo">
                        @elseif($heroUrl !== '')
                            <img class="fc-hero__img" src="{{ $heroUrl }}" alt="Card photo" loading="{{ $thumb ? 'lazy' : 'eager' }}" decoding="async">
                        @endif
                    </div>
                    <div class="fc-crest" aria-hidden="true">
                        <div class="fc-crest__inner">
                            <span class="fc-crest__mono fc-metal {{ $isPair ? 'is-pair' : '' }}"
                                  @if($live) x-text="c.monogram" :class="{ 'is-pair': c.monogram.length > 1 }" @endif>{{ $mono }}</span>
                        </div>
                    </div>
                    <div class="fc-seal" aria-hidden="true">
                        <span class="fc-seal__mono fc-metal {{ $isPair ? 'is-pair' : '' }}"
                              @if($live) x-text="c.monogram" :class="{ 'is-pair': c.monogram.length > 1 }" @endif>{{ $mono }}</span>
                    </div>
                </figure>

                <h2 class="fc-headline" @if($live) x-text="c.headline" @endif>{{ $card['headline'] ?? '' }}</h2>

                @if($live)
                    <p class="fc-recipient fc-metal" x-show="c.recipient" x-text="'Dearest ' + c.recipient + ','"></p>
                @elseif(!empty($card['recipient_name']))
                    <p class="fc-recipient fc-metal">Dearest {{ $card['recipient_name'] }},</p>
                @endif

                <p class="fc-verse" @if($live) x-text="c.verse" @endif>{{ $card['verse'] ?? '' }}</p>

                <div class="fc-sign">
                    <span class="fc-signoff" @if($live) x-text="c.signOff" @endif>{{ $card['sign_off'] ?? '' }}</span>
                    <strong class="fc-sender fc-metal" @if($live) x-text="c.sender" @endif>{{ $card['sender_name'] ?? '' }}</strong>
                </div>
            </div>

            <div class="fc-holo" aria-hidden="true"></div>
            <div class="fc-glare" aria-hidden="true"></div>
            @unless($thumb)
                <div class="fc-brand" aria-hidden="true">FamilyPlatform</div>
            @endunless
        </div>
    </div>
</article>
