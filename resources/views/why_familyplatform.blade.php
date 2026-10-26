@extends('layouts.landing_layout')

@section('title', 'Why FamilyPlatform vs WhatsApp | The Living Dynasty Sanctuary')
@section('meta_description', 'Discover why families are choosing FamilyPlatform over chaotic WhatsApp groups. Preserve uncompressed memories, living family trees, elder voice capsules, and structured reunions.')
@section('data-page-id', 'why-familyplatform')

@section('extra_css')
<style>
  .why-hero-section {
    background: linear-gradient(180deg, rgba(163, 22, 33, 0.05) 0%, rgba(243, 242, 239, 0.4) 100%);
    padding: 3.5rem 1rem 2.5rem;
    text-align: center;
  }

  .why-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--brand-primary);
    background: rgba(163, 22, 33, 0.08);
    padding: 6px 16px;
    border-radius: 9999px;
    margin-bottom: 1.25rem;
    border: 1px solid rgba(163, 22, 33, 0.2);
  }

  .why-headline {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(2rem, 4vw, 2.85rem);
    font-weight: 700;
    line-height: 1.25;
    color: #1e293b;
    margin-bottom: 1rem;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
  }

  .why-headline span {
    color: var(--brand-primary);
  }

  .why-lead {
    font-size: 1.15rem;
    color: var(--text-muted-aa, #475569);
    max-width: 780px;
    margin: 0 auto 2rem;
    line-height: 1.6;
  }

  .comparison-wrapper {
    max-width: 1200px;
    margin: 0 auto 4rem;
    padding: 0 1rem;
  }

  .card-compare {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
    padding: 2rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: all 0.25s ease;
  }

  .card-compare.wa-card {
    border-top: 5px solid #25d366;
  }

  .card-compare.sanctuary-card {
    border-top: 5px solid var(--brand-primary);
    box-shadow: 0 12px 28px -4px rgba(163, 22, 33, 0.12);
  }

  .compare-pill {
    font-size: 0.8rem;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .wa-pill {
    background: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #c8e6c9;
  }

  .sanctuary-pill {
    background: rgba(163, 22, 33, 0.08);
    color: var(--brand-primary);
    border: 1px solid rgba(163, 22, 33, 0.2);
  }

  .wa-stream-box {
    background: #0b141a;
    border-radius: 12px;
    padding: 1rem;
    color: #e9edef;
    font-size: 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 1rem 0;
    max-height: 380px;
    overflow-y: auto;
  }

  .wa-bubble {
    padding: 8px 12px;
    border-radius: 8px;
    line-height: 1.4;
    font-size: 0.82rem;
    max-width: 90%;
  }

  .wa-bubble.in {
    background: #202c33;
    align-self: flex-start;
    border-top-left-radius: 0;
  }

  .wa-bubble.out {
    background: #005c4b;
    align-self: flex-end;
    border-top-right-radius: 0;
  }

  .wa-bubble .sender {
    font-size: 0.72rem;
    font-weight: 700;
    color: #53bdeb;
    margin-bottom: 2px;
  }

  .wa-bubble .time {
    font-size: 0.65rem;
    color: #8696a0;
    float: right;
    margin-top: 4px;
    margin-left: 8px;
  }

  .sanctuary-stream-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin: 1rem 0;
  }

  .vault-card-sample {
    background: #ffffff;
    border: 1.5px solid rgba(163, 22, 33, 0.15);
    border-radius: 12px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  }

  .vault-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid var(--brand-primary);
    background: url('https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80') center/cover;
    flex-shrink: 0;
  }

  .voice-sample-player {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .btn-play-sample {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--brand-primary);
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
  }

  .waveform-bars {
    flex: 1;
    height: 22px;
    display: flex;
    align-items: center;
    gap: 3px;
  }

  .wave-stick {
    width: 3px;
    background: var(--brand-primary);
    border-radius: 2px;
    opacity: 0.75;
    animation: waveBounce 1.2s infinite ease-in-out alternate;
  }

  @keyframes waveBounce {
    from { height: 6px; }
    to { height: 20px; }
  }

  .compare-list {
    list-style: none;
    padding: 0;
    margin: 1.25rem 0 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .compare-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 0.9rem;
    color: #475569;
    line-height: 1.5;
  }

  .compare-item i {
    font-size: 1.15rem;
    flex-shrink: 0;
    margin-top: 2px;
  }

  .compare-item.bad i {
    color: #dc2626;
  }

  .compare-item.good i {
    color: var(--brand-success);
  }

  /* Matrix Table */
  .matrix-section {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 2.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    margin-bottom: 4rem;
  }

  .table-matrix th {
    font-weight: 700;
    background: #f8fafc;
    padding: 14px 18px;
    border-bottom: 2px solid #e2e8f0;
  }

  .table-matrix td {
    padding: 14px 18px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
  }

  /* CTA Banner */
  .cta-banner-box {
    background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
    border-radius: 20px;
    padding: 3.5rem 2rem;
    color: #ffffff;
    text-align: center;
    box-shadow: 0 16px 36px rgba(163, 22, 33, 0.3);
    margin-bottom: 3rem;
  }

  .cta-banner-box h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(1.8rem, 3.5vw, 2.5rem);
    margin-bottom: 1rem;
  }

  .cta-banner-box p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
    max-width: 650px;
    margin: 0 auto 2rem;
  }
</style>
@endsection

@section('content')
<main>
  <!-- Hero Section -->
  <section class="why-hero-section">
    <div class="container">
      <div class="why-badge">
        <i class="fas fa-shield-alt"></i> Purpose-Built for Family Legacy
      </div>
      <h1 class="why-headline">
        Why Families Choose <span>FamilyPlatform</span> Rather Than WhatsApp Groups
      </h1>
      <p class="why-lead">
        WhatsApp was built for fleeting, rapid text messaging. FamilyPlatform is your family's living sanctuary—preserving oral histories, uncompressed memories, multi-generational kinship, and structured celebrations that never get lost.
      </p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="/register" class="btn btn-brand btn-lg">
          <i class="fas fa-crown me-2"></i>Create Your Family Sanctuary
        </a>
        <a href="/login" class="btn btn-brand-outline btn-lg">
          <i class="fas fa-sign-in-alt me-2"></i>Sign In to Existing Tree
        </a>
      </div>
    </div>
  </section>

  <!-- Interactive Comparison Grid -->
  <section class="comparison-wrapper">
    <div class="row g-4">
      
      <!-- LEFT: WhatsApp Group Flaws -->
      <div class="col-lg-6">
        <div class="card-compare wa-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="compare-pill wa-pill">
              <i class="fab fa-whatsapp"></i> WhatsApp Family Group
            </span>
            <span class="text-danger fw-semibold small">
              <i class="fas fa-exclamation-triangle me-1"></i> 48-Hour Memory Half-Life
            </span>
          </div>

          <h3 class="h5 fw-bold text-dark mb-2">The Ephemeral Firehose</h3>
          <p class="text-muted small mb-3">
            Heirloom recipes, milestone photos, and reunion details drown beneath stickers, forwards, and political debates.
          </p>

          <div class="wa-stream-box">
            <div class="wa-bubble in">
              <div class="sender">Uncle Wale</div>
              Can everyone upload their photos from Grandma's 80th birthday? Please don't forget!
              <span class="time">10:14</span>
            </div>

            <div class="wa-bubble in">
              <div class="sender">Auntie Bimbo</div>
              [Blurry compressed photo] Grandma cutting cake! 🎉🎂
              <span class="time">10:18</span>
            </div>

            <div class="wa-bubble in">
              <div class="sender">Cousin Segun</div>
              [Good Morning Blessed Thursday GIF] 🙏✨
              <span class="time">10:22</span>
            </div>

            <div class="p-2 text-center text-danger-emphasis bg-danger-subtle rounded small my-1">
              ⚠️ 142 messages, forwarded memes &amp; stickers later...
            </div>

            <div class="wa-bubble in">
              <div class="sender">Cousin Tolu</div>
              Wait, what time are we meeting on Saturday? And who has Grandma's secret spiced beef recipe? I can't find it anywhere in this chat!
              <span class="time">18:45</span>
            </div>

            <div class="wa-bubble in">
              <div class="sender">Auntie Toyin</div>
              I posted the recipe in June! Now it says "Media expired from device". Someone please repost! 🤦‍♀️
              <span class="time">18:49</span>
            </div>

            <div class="wa-bubble out">
              You: WhatsApp doesn't preserve our family history. Everything gets buried or deleted!
              <span class="time">18:52</span>
            </div>
          </div>

          <ul class="compare-list">
            <li class="compare-item bad">
              <i class="fas fa-times-circle"></i>
              <div><strong>Chronic Amnesia:</strong> Photos and documents expire or become unsearchable after 48 hours.</div>
            </li>
            <li class="compare-item bad">
              <i class="fas fa-times-circle"></i>
              <div><strong>Generational Disconnect:</strong> Elders feel overwhelmed by constant notifications; children under 13 cannot safely participate.</div>
            </li>
            <li class="compare-item bad">
              <i class="fas fa-times-circle"></i>
              <div><strong>Awkward Privacy Boundaries:</strong> In-laws and distant branches are forced into one noisy room, or splinter into 10 disjointed groups.</div>
            </li>
          </ul>
        </div>
      </div>

      <!-- RIGHT: FamilyPlatform Sanctuary -->
      <div class="col-lg-6">
        <div class="card-compare sanctuary-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="compare-pill sanctuary-pill">
              <i class="fas fa-shield-alt"></i> FamilyPlatform Sanctuary
            </span>
            <span class="text-success fw-semibold small">
              <i class="fas fa-check-circle me-1"></i> Preserved for 100+ Years
            </span>
          </div>

          <h3 class="h5 fw-bold text-dark mb-2">The Permanent Dynasty Vault</h3>
          <p class="text-muted small mb-3">
            Every memory is tethered to your interactive family tree, organised by branch, and archived in high definition.
          </p>

          <div class="sanctuary-stream-box">
            <!-- Sample Milestone -->
            <div class="vault-card-sample">
              <div class="vault-avatar"></div>
              <div>
                <h4 class="h6 fw-bold mb-1 text-dark">Grandma Abigail's 80th Jubilee</h4>
                <p class="text-danger small mb-0 fw-semibold">32 Full-Resolution Photos • 18 Relatives Tagged</p>
              </div>
            </div>

            <!-- Sample Voice Player -->
            <div class="voice-sample-player">
              <button class="btn-play-sample" type="button" onclick="playVoiceDemo(this)" aria-label="Play sample oral history recording">
                <i class="fas fa-play"></i>
              </button>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between small mb-1">
                  <span class="fw-bold text-dark">Oral History: Arriving in London (1968)</span>
                  <span class="text-danger fw-semibold">03:42</span>
                </div>
                <div class="waveform-bars">
                  <div class="wave-stick" style="animation-delay: 0.1s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.3s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.5s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.2s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.4s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.6s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.1s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.3s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.5s;"></div>
                  <div class="wave-stick" style="animation-delay: 0.7s;"></div>
                </div>
              </div>
            </div>

            <!-- Structured Reunion Progress -->
            <div class="p-3 bg-white rounded border">
              <div class="d-flex justify-content-between small fw-bold mb-1">
                <span>Annual Family Reunion 2026</span>
                <span class="text-success"><i class="fas fa-check-circle me-1"></i>18 / 24 Confirmed</span>
              </div>
              <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 78%;" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-success-subtle text-success border border-success-subtle">Drinks: Uncle Wale</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Jollof Rice: Auntie Bimbo</span>
                <span class="badge bg-secondary-subtle text-secondary border">Music &amp; Games: Segun</span>
              </div>
            </div>
          </div>

          <ul class="compare-list">
            <li class="compare-item good">
              <i class="fas fa-check-circle"></i>
              <div><strong>Interactive Living Organogram:</strong> Complete genealogical mapping—every photo, video, and story connects to ancestral roots.</div>
            </li>
            <li class="compare-item good">
              <i class="fas fa-check-circle"></i>
              <div><strong>Elder Voice &amp; Time Capsules:</strong> Record spoken stories from grandparents before they are lost, locked until milestone years.</div>
            </li>
            <li class="compare-item good">
              <i class="fas fa-check-circle"></i>
              <div><strong>Maiden Lineage Isolation:</strong> Complete privacy across maternal and paternal branches without family awkwardness.</div>
            </li>
          </ul>
        </div>
      </div>

    </div>
  </section>

  <!-- Detailed Feature Matrix -->
  <section class="container">
    <div class="matrix-section">
      <div class="text-center mb-4">
        <h2 class="h3 fw-bold text-dark">Feature by Feature: Side-by-Side Breakdown</h2>
        <p class="text-muted">See how FamilyPlatform transforms every aspect of family engagement.</p>
      </div>

      <div class="table-responsive">
        <table class="table table-matrix table-hover mb-0">
          <thead>
            <tr>
              <th scope="col" style="width: 30%;">Capability</th>
              <th scope="col" style="width: 35%; color: #166534;"><i class="fab fa-whatsapp me-2"></i>WhatsApp Family Group</th>
              <th scope="col" style="width: 35%; color: var(--brand-primary);"><i class="fas fa-shield-alt me-2"></i>FamilyPlatform Sanctuary</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Memory Half-Life</strong></td>
              <td class="text-muted"><i class="fas fa-times text-danger me-2"></i>48 Hours (Buried under memes &amp; stickers)</td>
              <td class="fw-semibold text-dark"><i class="fas fa-check text-success me-2"></i>Permanent Heirloom (Mapped to tree nodes)</td>
            </tr>
            <tr>
              <td><strong>Media Quality</strong></td>
              <td class="text-muted"><i class="fas fa-times text-danger me-2"></i>Compressed, degraded, and prone to device expiry</td>
              <td class="fw-semibold text-dark"><i class="fas fa-check text-success me-2"></i>Full 4K resolution with zero loss</td>
            </tr>
            <tr>
              <td><strong>Family Tree / Kinship</strong></td>
              <td class="text-muted"><i class="fas fa-times text-danger me-2"></i>None (Flat, chaotic phone number roster)</td>
              <td class="fw-semibold text-dark"><i class="fas fa-check text-success me-2"></i>Interactive Organogram &amp; Kinship Calculator</td>
            </tr>
            <tr>
              <td><strong>Event Planning</strong></td>
              <td class="text-muted"><i class="fas fa-times text-danger me-2"></i>60 conflicting texts ("Can you do Saturday?")</td>
              <td class="fw-semibold text-dark"><i class="fas fa-check text-success me-2"></i>Structured RSVP, calendar sync &amp; potluck delegation</td>
            </tr>
            <tr>
              <td><strong>Inclusivity (Kids &amp; Elders)</strong></td>
              <td class="text-muted"><i class="fas fa-times text-danger me-2"></i>Children under 13 and non-smartphone elders excluded</td>
              <td class="fw-semibold text-dark"><i class="fas fa-check text-success me-2"></i>Supervised kid accounts &amp; elder voice capsules</td>
            </tr>
            <tr>
              <td><strong>Lineage Privacy</strong></td>
              <td class="text-muted"><i class="fas fa-times text-danger me-2"></i>In-laws forced into one room or splinter groups</td>
              <td class="fw-semibold text-dark"><i class="fas fa-check text-success me-2"></i>Maiden Lineage Isolation across maternal/paternal branches</td>
            </tr>
            <tr>
              <td><strong>Oral History &amp; Recipes</strong></td>
              <td class="text-muted"><i class="fas fa-times text-danger me-2"></i>Lost in chat history</td>
              <td class="fw-semibold text-dark"><i class="fas fa-check text-success me-2"></i>Living Journal &amp; Milestone Time Capsules</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- High-Converting CTA Banner -->
  <section class="container">
    <div class="cta-banner-box">
      <h2>Begin Preserving Your Family Legacy Today</h2>
      <p>
        Don't let your family's precious milestones, oral histories, and ancestral roots vanish into a fleeting chat stream. Anchor your dynasty on FamilyPlatform.
      </p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="/register" class="btn btn-light btn-lg text-dark fw-bold px-4">
          <i class="fas fa-plus-circle me-2 text-danger"></i>Create Your Family Sanctuary
        </a>
        <a href="/login" class="btn btn-outline-light btn-lg px-4">
          <i class="fas fa-users me-2"></i>Join Existing Family
        </a>
      </div>
    </div>
  </section>
</main>

<script>
  function playVoiceDemo(btn) {
    const icon = btn.querySelector('i');
    if (icon.classList.contains('fa-play')) {
      icon.classList.remove('fa-play');
      icon.classList.add('fa-pause');
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'info',
          title: 'Playing Grandma Abigail (1968 Oral History)...',
          showConfirmButton: false,
          timer: 3000
        });
      }
    } else {
      icon.classList.remove('fa-pause');
      icon.classList.add('fa-play');
    }
  }
</script>
@endsection
