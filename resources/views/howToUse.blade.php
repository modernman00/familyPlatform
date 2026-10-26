@extends('layouts.landing_layout')

@section('title', 'Living Product Guide — How to Use FamilyPlatform')
@section('data-page-id', 'how-to-use')

@section('content')
  <!-- Header Banner -->
  <header class="doc-center-header" style="background: linear-gradient(135deg, #7a0e16 0%, #a31621 100%); padding: 60px 0; color: #ffffff; text-align: center;">
    <div class="container">
      <h1 class="display-5 fw-bold mb-2">Living Product Guide</h1>
      <p class="fs-5 text-white-50 mb-0">Master your family tree, preserve oral heirlooms, and connect generations.</p>
    </div>
  </header>

  <!-- Content Container -->
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">

        <!-- Pillar 1: 7-Generation Dynasty Navigation -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(163,22,33, 0.12); color: #a31621;">
                <i class="bi bi-diagram-3-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">1. Navigating Your 7-Generation Dynasty Tree</h3>
                <span class="badge bg-teal text-white" style="background: #a31621;">Core Architecture</span>
              </div>
            </div>

            <p class="lead text-secondary">
              FamilyPlatform provides a panoramic, ego-centric organogram canvas rendering up to 7 generations upwards (-1 to -7) and downwards (+1 to +7) with complete collateral and spouse integration.
            </p>

            <div class="row g-4 mt-2">
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border h-100">
                  <h6 class="fw-bold text-dark"><i class="bi bi-arrow-up-circle-fill text-primary me-2"></i>Upwards Ancestry (-1 to -7)</h6>
                  <p class="small text-muted mb-0">
                    Traverse from Parents (-1) through Grandparents (-2), Great-Grandparents (-3), up to 5th Great-Grandparents (-7). Both paternal and maternal unions are rendered symmetrically without node overlap.
                  </p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border h-100">
                  <h6 class="fw-bold text-dark"><i class="bi bi-arrow-down-circle-fill text-success me-2"></i>Downwards Descendants (+1 to +7)</h6>
                  <p class="small text-muted mb-0">
                    Explore your Children (+1), Grandchildren (+2), Great-Grandchildren (+3), down to 5th Great-Grandchildren (+7), including all partners, spouses, and collateral sibling downlines.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pillar 2: Kinship Auto-Matching Resolution -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(163,22,33, 0.12); color: #a31621;">
                <i class="bi bi-person-check-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">2. Intelligent Kinship Auto-Matching ("Is This You?")</h3>
                <span class="badge bg-primary">New in v9.2</span>
              </div>
            </div>

            <p class="lead text-secondary">
              When a relative registers independently or readjusts their Family Code, our multi-vector entity resolution engine automatically pairs them with their position on the live tree.
            </p>

            <div class="alert alert-info border-0 rounded-3 mb-4" style="background: rgba(163,22,33, 0.08);">
              <h6 class="fw-bold text-primary mb-1"><i class="bi bi-magic me-2"></i>How the Auto-Matching Engine Works:</h6>
              <ul class="mb-0 small text-dark ps-3">
                <li><strong>Yoruba & Anglo Diminutive AI:</strong> Recognizes nicknames like <em>Femi &harr; Olufemi</em>, <em>Wale &harr; Adewale</em>, <em>Segun &harr; Olusegun</em>, and <em>Kenny &harr; Kehinde</em>.</li>
                <li><strong>Kinship Context Prompt:</strong> Displays the exact family relation: <em>"We found Femi Olaogun (Brother of Wale • Son of Adesanya & Iyabo). Is this you?"</em></li>
                <li><strong>Single-Click Grafting:</strong> If you already added children or a spouse, clicking <strong>"Yes, Connect My Account"</strong> automatically grafts your entire subtree onto the family tree without duplication.</li>
                <li><strong>Adversarial IDOR & Concurrency Guards:</strong> Row-level pessimistic locks (<code>FOR UPDATE</code>) and tenant isolation guarantee zero duplicate claims or cross-family access.</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Pillar 3: Living Voice Capsules -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <i class="bi bi-mic-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">3. Living Voice Capsules (Oral Heritage)</h3>
                <span class="badge bg-warning text-dark">Audio Preservation</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Photographs show how ancestors looked; voice capsules preserve who they were. Attach high-fidelity spoken stories directly to any family node.
            </p>

            <ol class="text-secondary ps-3 mb-4">
              <li class="mb-2">Click on any relative's card on the family tree canvas.</li>
              <li class="mb-2">Click <strong>"Record / Upload Voice Capsule"</strong> in the slide-out dossier.</li>
              <li class="mb-2">Record oral histories, lullabies, ancestral blessings, or life advice.</li>
              <li>Recordings are encrypted and streamable exclusively by authenticated family members.</li>
            </ol>
          </div>
        </div>

        <!-- Pillar 4: Multi-Touch Onboarding Lifecycle -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="bi bi-envelope-heart-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">4. Family Code Sharing & Lifecycle Flywheel</h3>
                <span class="badge bg-success">Automated Growth</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Every family is assigned a unique, prestigious <strong>Family Code</strong>. Sharing this code is the fastest way to assemble your dynasty.
            </p>

            <div class="row g-3">
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-teal text-white mb-2" style="background: #a31621;">Touch 1 (Day 1)</span>
                  <h6 class="fw-bold text-dark">Lineage Kickstart</h6>
                  <p class="small text-muted mb-0">Helpful tips via Email and WebPush guiding new members to add parents and siblings in under 60 seconds.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-warning text-dark mb-2">Touch 2 (Day 3)</span>
                  <h6 class="fw-bold text-dark">Voice Heirlooms</h6>
                  <p class="small text-muted mb-0">Curated interview questions to ask elders, preserving authentic oral stories before memories fade.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-primary mb-2">Touch 3 (Day 7)</span>
                  <h6 class="fw-bold text-dark">WhatsApp Flywheel</h6>
                  <p class="small text-muted mb-0">One-click WhatsApp invite generator enabling relatives to join and claim their spot on your family tree.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pillar 4b: Birth / Maiden Family Code (RFC-0004) -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden" id="playbook-birth-family">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(184,32,44, 0.12); color: #b8202c;">
                <i class="bi bi-diagram-3-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">4b. Your Birth / Maiden Family Code</h3>
                <span class="badge" style="background:#b8202c;">Dual Lineage</span>
              </div>
            </div>
            <p class="lead text-secondary">
              Married women keep two family circles: the household they live in and the family they were born into. Your household code never changes; your birth family gets its own code.
            </p>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-primary mb-2">Step 1 &middot; Settings &rarr; Family Network</span>
                  <h6 class="fw-bold text-dark">Create or link</h6>
                  <p class="small text-muted mb-0"><strong>Create</strong> a new code from your maiden name if your parents and siblings are not here yet, or <strong>link</strong> to an existing code by naming a family member who can confirm you.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-warning text-dark mb-2">Step 2 &middot; Verification</span>
                  <h6 class="fw-bold text-dark">Only families you belong to</h6>
                  <p class="small text-muted mb-0">A code you created is yours for good. A linked code needs approval from a real member of that family, and you can unlink it any time without deleting their tree.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-success mb-2">Step 3 &middot; Parents &amp; Siblings tabs</span>
                  <h6 class="fw-bold text-dark">Invite with the right code</h6>
                  <p class="small text-muted mb-0">Choose <em>My Birth / Maiden Family</em> when adding parents or siblings. Invitations and new relatives land in your birth family tree, not your household tree.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pillar 4c: Change the Family Code by Family Vote (RFC-0005) -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden" id="playbook-code-rename">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(163,22,33, 0.12); color: #a31621;">
                <i class="bi bi-ui-checks-grid fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">4c. Changing Your Family Code</h3>
                <span class="badge" style="background:#a31621;">Family Vote</span>
              </div>
            </div>
            <p class="lead text-secondary">
              The person who created the family code can propose a new one, but never alone: the whole family votes first. If more members vote yes than no, the code changes for everyone automatically.
            </p>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-primary mb-2">Step 1 &middot; Settings &rarr; Family Network</span>
                  <h6 class="fw-bold text-dark">Creator proposes</h6>
                  <p class="small text-muted mb-0">Type the new code (4&ndash;12 letters or numbers) and tap <em>Ask my family to vote</em>. A poll appears in the family feed and every member is notified.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-warning text-dark mb-2">Step 2 &middot; Seven days to vote</span>
                  <h6 class="fw-bold text-dark">Most of the family must agree</h6>
                  <p class="small text-muted mb-0">Each member has one vote and can change it until the vote closes. When voting ends, the change goes ahead if more members voted yes than no (and at least one member besides the creator voted), so members who never vote do not hold it back. If over half the family says yes it happens straight away; if no votes reach half the family it stops.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-success mb-2">Step 3 &middot; Automatic</span>
                  <h6 class="fw-bold text-dark">Everyone moves together</h6>
                  <p class="small text-muted mb-0">Your tree, posts, events, journals and invites move to the new code and everyone is notified. Old invite links keep working. The code can be changed once every 30 days.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pillar 5: Gamified Lineage Quests & WhatsApp Flywheel -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="bi bi-compass-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">5. "The Missing Link" WhatsApp Quests</h3>
                <span class="badge bg-success">Viral Acquisition</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Turn incomplete family branches into exciting family sleuthing missions. The platform scans your tree, identifies gaps (missing grandparents, unrecorded maiden names, or unclaimed cousins), and provides pre-composed WhatsApp questions.
            </p>

            <div class="p-3 rounded-3 bg-light border mb-3">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-whatsapp text-success fs-5"></i>
                <span class="fw-bold text-dark">1-Tap WhatsApp Collaboration</span>
              </div>
              <p class="small text-muted mb-0">
                Click <strong>"Quests"</strong> on your organogram canvas to view active missions. Tap <strong>"Ask on WhatsApp"</strong> to instantly message Uncle Tunde or Auntie Funke with an opaque, 256-bit signed claim link that pre-fills their details in 10 seconds.
              </p>
            </div>
          </div>
        </div>

        <!-- Pillar 6: Sunday Family Trivia & "Guess Who" -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <i class="bi bi-trophy-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">6. Sunday Family Trivia &amp; "Guess Who"</h3>
                <span class="badge bg-warning text-dark">Weekly Ritual</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Every Sunday, FamilyPlatform automatically drops an authentic heritage trivia poll generated directly from your family's records. Test how well cousins know their roots, vote in the feed, and debate in the comments!
            </p>

            <div class="p-3 rounded-3 bg-light border">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-stars text-warning fs-5"></i>
                <span class="fw-bold text-dark">Custom "Guess Who" Challenges</span>
              </div>
              <p class="small text-muted mb-0">
                Any family member can post a throwback photo challenge using the <strong>"👶 Guess Who"</strong> or <strong>"🧠 Family Lore"</strong> templates in the post composer to spark spirited multi-generational debates.
              </p>
            </div>
          </div>
        </div>

        <!-- Pillar 7: Dynamic WhatsApp Family Status Flyer -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(163,22,33, 0.12); color: #a31621;">
                <i class="bi bi-card-image fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">7. WhatsApp Family Status Flyer</h3>
                <span class="badge bg-primary">Social Proof &amp; Pride</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Celebrate your family dynasty with a luxury heraldic status card. Rendered 100% client-side via HTML5 Canvas, this high-resolution graphic summarizes your total connected members, generations recorded, and cities spanned.
            </p>

            <div class="p-3 rounded-3 bg-light border">
              <p class="small text-muted mb-0">
                Click <strong>"WhatsApp Flyer"</strong> on the organogram to preview your clan crest. Use <strong>"Share to WhatsApp"</strong> on mobile to pass the image directly into family group chats via the native Web Share API.
              </p>
            </div>
          </div>
        </div>

        <!-- Pillar 8: Living Heritage Time Capsule Vault -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(184,32,44, 0.12); color: #ef6b73;">
                <i class="bi bi-hourglass-split fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">8. Digital Time Capsule Vault</h3>
                <span class="badge" style="background: #ef6b73; color: #fff;">Generational Preservation</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Seal personal letters, prayers, and words of wisdom for children and grandchildren locked until future milestone dates (such as an 18th/21st birthday, graduation, or wedding).
            </p>

            <div class="p-3 rounded-3 bg-light border">
              <p class="small text-muted mb-0">
                All time capsule contents are cryptographically sealed with zero-trust server-side redaction—preserving surprises and anticipation until the exact arrival date.
              </p>
            </div>
          </div>
        </div>

        <!-- Pillar 9: Privacy, Two-Factor Authentication (2FA) & Passkeys -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(163,22,33, 0.12); color: #0ea5e9;">
                <i class="bi bi-shield-lock-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">9. Privacy, Two-Factor Authentication &amp; Passkeys</h3>
                <span class="badge bg-info text-dark">Account Security</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Control how you sign in and who can view your profile inside your family sanctuary from <strong>Account Settings &rarr; Privacy</strong>.
            </p>

            <div class="p-3 rounded-3 bg-light border">
              <p class="small text-muted mb-0">
                Toggle <strong>Two-Factor Authentication (2FA)</strong> <strong>ON</strong> to require a 6-digit email verification code whenever you sign in with your password, or switch it <strong>OFF</strong> for direct password sign-in. You can also register <strong>FaceID / TouchID Passkeys</strong> for instant 1-tap biometric login.
              </p>
            </div>
          </div>
        </div>

        <!-- Pillar 10: Family Chronicles, Announcements & Public Blog -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(163,22,33, 0.12); color: #a31621;">
                <i class="bi bi-journal-richtext fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">10. Family Chronicles, Announcements &amp; Public Blog</h3>
                <span class="badge" style="background: #a31621; color: #fff;">Storytelling &amp; Insights</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Members can preserve rich family chronicles with the built-in AI Story Assistant at <strong>Family Journal</strong>, broadcast urgent family notices, and explore public heritage articles on our <a href="/blogs" class="fw-bold text-decoration-none" style="color: #7a0e16;">Public Blog</a>.
            </p>

            <div class="p-3 rounded-3 bg-light border">
              <p class="small text-muted mb-0">
                Read public insights on <a href="/blogs" class="fw-semibold">FamilyPlatform Blog</a>, or craft private member journals within your family enclave.
              </p>
            </div>
          </div>
        </div>

        <!-- Back to Organogram CTA -->
        <div class="text-center mt-5">
          <a href="/organogram" class="btn btn-lg fw-bold px-5 py-3 text-white shadow-sm" style="background: linear-gradient(135deg, #a31621 0%, #7a0e16 100%); border-radius: 50px;">
            <i class="bi bi-diagram-3-fill me-2"></i>Explore Your Family Tree
          </a>
        </div>

      </div>
    </div>
  </main>
@endsection
