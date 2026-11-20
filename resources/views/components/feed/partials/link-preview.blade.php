{{-- @props(["post" => null]) --}}
  <!-- Full Graphics OpenGraph Social Card (16:9) (Suppressed for Linked Native Photos & Videos & 3D Stage Posts) -->
  <template x-if="post.link_preview && (post.link_preview.image || post.link_preview.title) && !(post.postMessage || '').includes('/familyJournal/') && !post.link_preview.is_photo_link && !post.link_preview.is_google_photo && !post.link_preview.is_video && (!post.images || post.images.length === 0) && (!post.videos || post.videos.length === 0) && !post.video && !getPostStageMeta(post)">
    <div class="link-preview-container mb-3 px-1">
      <a :href="post.link_preview.url" 
         target="_blank" 
         rel="noopener noreferrer" 
         class="card border text-decoration-none overflow-hidden link-preview-card shadow-sm"
         style="border-radius: 14px; background-color: var(--bs-card-bg, #ffffff); border-color: var(--bs-border-color, #e2e8f0); transition: transform 0.2s ease, box-shadow 0.2s ease; display: block;">
        
        <!-- Full 16:9 Graphics Banner -->
        <template x-if="post.link_preview.image">
          <div class="position-relative overflow-hidden w-100" style="aspect-ratio: 16 / 9; background-color: #0f172a; max-height: 420px;">
            <img :src="post.link_preview.image" 
                 class="w-100 h-100 object-fit-cover" 
                 :alt="post.link_preview.title || 'Event preview image'"
                 loading="lazy"
                 onerror="this.style.display='none'">
            
            <!-- Platform Badge Pill -->
            <div class="position-absolute top-0 start-0 m-2.5">
              <span class="badge rounded-pill text-uppercase px-2.5 py-1.5 shadow-sm d-inline-flex align-items-center gap-1"
                    style="background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(8px); font-size: 0.72rem; letter-spacing: 0.5px; border: 1px solid rgba(255, 255, 255, 0.18); color: #ffffff;">
                <template x-if="post.link_preview.is_party_event">
                  <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-balloon-fill text-warning"></i> PartyPlatform</span>
                </template>
                <template x-if="!post.link_preview.is_party_event">
                  <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-link-45deg"></i> <span x-text="post.link_preview.domain || 'External Link'"></span></span>
                </template>
              </span>
            </div>
          </div>
        </template>

        <!-- Card Meta Details -->
        <div class="p-3" style="background-color: var(--bs-card-bg, #ffffff);">
          <div class="text-uppercase text-muted fw-semibold small mb-1 d-flex align-items-center justify-content-between" style="font-size: 0.74rem; letter-spacing: 0.5px;">
            <span class="text-truncate" x-text="post.link_preview.domain || post.link_preview.site_name"></span>
            <span class="text-primary small d-inline-flex align-items-center gap-1">
              <span x-text="post.link_preview.is_party_event ? 'Celebration Pass' : 'Web Link'"></span>
              <i class="bi bi-box-arrow-up-right" style="font-size: 0.7rem;"></i>
            </span>
          </div>

          <h6 class="fw-bold mb-1.5 text-dark" style="font-size: 1.05rem; line-height: 1.35;" x-text="post.link_preview.title"></h6>

          <template x-if="post.link_preview.description">
            <p class="text-secondary small mb-2.5" style="line-height: 1.45; font-size: 0.86rem; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" x-text="post.link_preview.description"></p>
          </template>

          <div class="pt-2 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted text-truncate" style="font-size: 0.78rem; max-width: 60%;" x-text="post.link_preview.url"></span>
            <span class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                  style="min-height: 40px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;">
              <span x-text="post.link_preview.cta_label || 'View Event / RSVP →'"></span>
            </span>
          </div>
        </div>

      </a>
    </div>
  </template>
