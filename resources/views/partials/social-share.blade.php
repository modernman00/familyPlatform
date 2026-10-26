@php
    $appUrl = rtrim($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com', '/');
    $currentUrl = $shareUrl ?? ($appUrl . ($_SERVER['REQUEST_URI'] ?? '/'));
    $title = $shareTitle ?? 'FamilyPlatform Dynasty Sanctuary';
    $category = $shareCategory ?? 'general';

    // Tailored WhatsApp pre-formatted copy according to category
    if (!empty($shareText)) {
        $captionText = $shareText;
    } elseif ($category === 'invite') {
        $captionText = "👋 Hello! I have reserved your spot on our family tree on FamilyPlatform. Tap here to claim your branch and see how our generations connect:\n" . $currentUrl;
    } elseif ($category === 'milestone') {
        $captionText = "✨ A new family dynasty milestone was just archived in our FamilyPlatform vault!\n\n" . $title . "\nTap to view the full-resolution photos and memories:\n" . $currentUrl;
    } elseif ($category === 'recipe') {
        $captionText = "🍲 An ancestral family recipe has been preserved in our FamilyPlatform Recipe Journal!\n\n" . $title . "\nTap to view the ingredients and techniques:\n" . $currentUrl;
    } elseif ($category === 'voice') {
        $captionText = "🎙️ Listen to this elder oral history voice recording preserved on FamilyPlatform:\n\n" . $title . "\nTap to listen:\n" . $currentUrl;
    } elseif ($category === 'event') {
        $captionText = "📅 Family celebration update: " . $title . "\nPlease confirm your RSVP and view potluck delegations on FamilyPlatform:\n" . $currentUrl;
    } else {
        $captionText = "🌿 Cherished moments from our Family Sanctuary:\n" . $title . "\n" . $currentUrl;
    }

    $encodedWaText = rawurlencode($captionText);
    $waShareUrl = "https://api.whatsapp.com/send?text=" . $encodedWaText;
    $uniqueShareId = 'share_' . bin2hex(random_bytes(4));
@endphp

<div class="family-social-share-widget my-3" id="{{ $uniqueShareId }}">
  @if(empty($compact))
  <div class="card p-3 border rounded-3 bg-white shadow-xs">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill" style="background: rgba(37, 211, 102, 0.15); color: #166534; font-weight: 700; font-size: 0.75rem; padding: 4px 10px;">
          <i class="fab fa-whatsapp me-1"></i>WhatsApp Bridge
        </span>
        <span class="small text-muted">Share to Family Group</span>
      </div>
      <small class="text-muted"><i class="fas fa-lock me-1 text-success"></i>Private &amp; Secure</small>
    </div>
    <p class="small text-muted mb-3">
      Keep your family group chat informed without losing memories. Tap below to send this directly into WhatsApp.
    </p>
  @endif

    <div class="d-flex gap-2 flex-wrap align-items-center">
      <!-- 1-Tap WhatsApp Button -->
      <a href="{{ $waShareUrl }}" 
         target="_blank" 
         rel="noopener noreferrer" 
         class="btn text-white fw-bold d-inline-flex align-items-center gap-2 shadow-xs" 
         style="background: #25d366; border-radius: 10px; padding: 0.5rem 1.1rem; min-height: 44px; text-decoration: none;"
         title="Share directly to WhatsApp family chat">
        <i class="fab fa-whatsapp fs-5"></i>
        <span>Share on WhatsApp</span>
      </a>

      <!-- Native Web Share API (Mobile Devices) -->
      <button type="button" 
              class="btn btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-2" 
              style="border-radius: 10px; min-height: 44px; padding: 0.5rem 1rem;"
              onclick="triggerPlatformWebShare('{{ addslashes($title) }}', '{{ addslashes($captionText) }}', '{{ addslashes($currentUrl) }}')">
        <i class="fas fa-share-alt"></i>
        <span>More Options</span>
      </button>

      <!-- Copy Link Fallback -->
      <button type="button" 
              class="btn btn-light border fw-semibold d-inline-flex align-items-center gap-2" 
              style="border-radius: 10px; min-height: 44px; padding: 0.5rem 1rem;"
              onclick="copyShareUrl('{{ addslashes($currentUrl) }}', this)">
        <i class="fas fa-copy text-muted"></i>
        <span class="btn-copy-label">Copy Link</span>
      </button>
    </div>

  @if(empty($compact))
  </div>
  @endif
</div>

<script>
  if (typeof window.triggerPlatformWebShare === 'undefined') {
    window.triggerPlatformWebShare = async function(title, text, url) {
      if (navigator.share) {
        try {
          await navigator.share({
            title: title,
            text: text,
            url: url
          });
        } catch (err) {
          if (err.name !== 'AbortError') {
            console.warn('[WebShare] Non-fatal error:', err);
          }
        }
      } else {
        // Fallback to clipboard copy
        navigator.clipboard.writeText(url).then(function() {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Link copied to clipboard!',
              showConfirmButton: false,
              timer: 2500
            });
          } else {
            alert('Link copied to clipboard: ' + url);
          }
        });
      }
    };
  }

  if (typeof window.copyShareUrl === 'undefined') {
    window.copyShareUrl = function(url, btn) {
      navigator.clipboard.writeText(url).then(function() {
        var label = btn.querySelector('.btn-copy-label');
        var originalText = label ? label.textContent : 'Copy Link';
        if (label) label.textContent = 'Copied!';
        btn.classList.add('btn-success', 'text-white');
        btn.classList.remove('btn-light');
        setTimeout(function() {
          if (label) label.textContent = originalText;
          btn.classList.remove('btn-success', 'text-white');
          btn.classList.add('btn-light');
        }, 2000);
      });
    };
  }
</script>
