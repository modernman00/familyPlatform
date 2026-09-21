@extends('baseBulma')

@section('title', 'Join FamilyPlatform — Your Private Family Sanctuary')

@section('data-page-id', 'joinRecommendation')

@section('og_tags')
    {{-- Referral landing page: personalised rich-preview card for WhatsApp / iMessage / Telegram --}}
    @php
        $ogInviter  = !empty($inviterName) ? htmlspecialchars(strip_tags($inviterName), ENT_QUOTES, 'UTF-8') : null;
        $ogTitle    = $ogInviter
            ? "{$ogInviter} invited you to FamilyPlatform 🔒"
            : 'Start Your Private Family Sanctuary — FamilyPlatform';
        $ogDesc     = 'Your own 100% private family network — secure family tree, milestone vault, and memories. Zero public feeds. Zero algorithms.';
        $ogImageUrl = rtrim(getenv('APP_URL') ?: 'https://myfamilyplatform.com', '/') . '/public/img/og-invite.jpg';
        $ogUrl      = rtrim(getenv('APP_URL') ?: 'https://myfamilyplatform.com', '/') . '/join' . (!empty($refToken) ? '?ref=' . urlencode($refToken) : '');
    @endphp
    <meta property="fb:app_id"        content="{{ $_ENV['FACEBOOK_APP_ID'] ?? $_ENV['FB_APP_ID'] ?? '' }}">
    <meta property="og:type"         content="website">
    <meta property="og:site_name"    content="FamilyPlatform">
    <meta property="og:title"        content="{{ $ogTitle }}">
    <meta property="og:description"  content="{{ $ogDesc }}">
    <meta property="og:image"        content="{{ $ogImageUrl }}">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"    content="FamilyPlatform — Your Private Family Sanctuary">
    <meta property="og:url"          content="{{ $ogUrl }}">
    {{-- Twitter / X --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDesc }}">
    <meta name="twitter:image"       content="{{ $ogImageUrl }}">
    <meta name="twitter:image:alt"   content="FamilyPlatform — Your Private Family Sanctuary">
@endsection

@section('content')

<style>
  .sanctuary-hero-wrap {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
  }

  .sanctuary-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(16px);
    border-radius: 20px;
    border: 1px solid rgba(99, 102, 241, 0.15);
    box-shadow: 0 20px 45px -10px rgba(79, 70, 229, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.03);
    max-width: 640px;
    width: 100%;
    padding: 2.5rem 2rem;
    text-align: center;
  }

  .sanctuary-icon-badge {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #312e81 100%);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
    margin-bottom: 1.5rem;
  }

  .sanctuary-title {
    font-size: 1.85rem;
    font-weight: 800;
    color: #1e1b4b;
    line-height: 1.25;
    margin-bottom: 0.75rem;
  }

  .sanctuary-inviter-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #eef2ff;
    color: #4338ca;
    padding: 0.4rem 1rem;
    border-radius: 9999px;
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
  }

  .sanctuary-usp-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin: 2rem 0;
    text-align: left;
  }

  @media (min-width: 576px) {
    .sanctuary-usp-grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  .usp-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
  }

  .usp-item-header {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.25rem;
  }

  .usp-item-desc {
    font-size: 0.825rem;
    color: #64748b;
    line-height: 1.4;
  }

  .btn-sanctuary-primary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.9rem 1.5rem;
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    color: #ffffff;
    font-size: 1.1rem;
    font-weight: 700;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.35);
    transition: all 0.2s ease;
  }

  .btn-sanctuary-primary:hover {
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 14px 28px -5px rgba(79, 70, 229, 0.45);
  }

  .sanctuary-footer-text {
    font-size: 0.85rem;
    color: #94a3b8;
    margin-top: 1.5rem;
  }
</style>

<div class="sanctuary-hero-wrap">
  <div class="sanctuary-card">
    
    <div class="sanctuary-icon-badge">
      🔒
    </div>

    @if(!empty($inviterName))
      <div class="sanctuary-inviter-pill">
        <span>✨ Recommended by <strong>{{ $inviterName }}</strong></span>
      </div>
    @endif

    <h1 class="sanctuary-title">
      Start Your Private Family Sanctuary
    </h1>

    <p style="color: #475569; font-size: 1.05rem; line-height: 1.5;">
      Zero algorithms. Zero public feeds. A secure digital vault designed exclusively to celebrate your family's legacy and memories.
    </p>

    <!-- The 4 Core USPs -->
    <div class="sanctuary-usp-grid">
      <div class="usp-item">
        <div class="usp-item-header">
          <span>🛡️</span> 100% Walled Sanctuary
        </div>
        <div class="usp-item-desc">
          Only connected kins and approved family members can ever see through your wall.
        </div>
      </div>

      <div class="usp-item">
        <div class="usp-item-header">
          <span>🌳</span> Your Own Family Tree
        </div>
        <div class="usp-item-desc">
          Build and explore your interactive organogram with your own private family code.
        </div>
      </div>

      <div class="usp-item">
        <div class="usp-item-header">
          <span>📸</span> Milestone Vault
        </div>
        <div class="usp-item-desc">
          Capture birthdays, anniversaries, and voice reels away from noisy public social media.
        </div>
      </div>

      <div class="usp-item">
        <div class="usp-item-header">
          <span>👑</span> Independent & Private
        </div>
        <div class="usp-item-desc">
          Your family network is completely private and decoupled from external connections.
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top: 2rem;">
      <a href="/createFamilyCode{{ !empty($refToken) ? '?ref=' . urlencode($refToken) : '' }}" class="btn-sanctuary-primary" id="btnCreateFamilyNetwork">
        <span>🚀 Create Your Family Network</span>
      </a>

      <div style="margin-top: 1rem;">
        <a href="/register{{ !empty($refToken) ? '?ref=' . urlencode($refToken) : '' }}" style="color: #4f46e5; font-size: 0.95rem; font-weight: 600; text-decoration: underline;">
          Joining an existing family? Register with a code
        </a>
      </div>
    </div>

    <div class="sanctuary-footer-text">
      🔒 FamilyPlatform enforces end-to-end multi-tenant isolation. Your family data is never shared.
    </div>

  </div>
</div>

@endsection
