{{-- @props(["post" => null]) --}}
  <!-- Tagged with You Heritage Ribbon -->
  <template x-if="isUserTaggedInPost(post)">
    <div class="d-flex align-items-center justify-content-between mb-2.5 py-1 px-2.5 rounded-2 shadow-2xs" style="background: rgba(163,22,33,0.06); border: 1px solid rgba(163,22,33,0.18);">
      <div class="d-flex align-items-center gap-1.5" style="color: #8a1019; font-size: 0.78rem; font-weight: 600;">
        <i class="bi bi-tag-fill" style="font-size: 0.82rem;"></i>
        <span>{{ __('feed.tagged_banner', 'You are tagged in this family memory') }}</span>
      </div>
      <span class="badge rounded-pill bg-white text-muted border px-2 py-0.5 shadow-2xs" style="font-size: 0.7rem; font-weight: 500;">
        {{ __('feed.living_memory', 'Living Memory') }}
      </span>
    </div>
  </template>

  <!-- Post Author Header -->
  <div class="d-flex align-items-center mb-3">
    <a :href="'/profilepage/img?dir=img&pics=' + ((post.images && post.images[0]) ? post.images[0] : '') + '&pID=' + post.post_no + '&path=profile&id=' + post.id">
      <img :src="post.profileImg" alt="img" class="rounded-circle me-3" style="width:40px; height:40px; object-fit: cover;">
    </a>
    <div class="flex-grow-1">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="d-flex align-items-center flex-wrap">
            <h3 class="h6 mb-0 fw-semibold text-dark" style="font-size: 1.05rem;" x-text="post.fullName"></h3>
            <template x-if="post.is_official || post.id === '{{ \App\services\FamilyPlatformAccountService::getAccountId() }}'">
              <span class="badge rounded-pill text-white ms-1.5 px-2 py-0.5 d-inline-flex align-items-center gap-1 shadow-2xs" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); font-size: 0.72rem; font-weight: 600;" title="Verified FamilyPlatform Account">
                <i class="bi bi-patch-check-fill" style="color: #ffffff; font-size: 0.75rem;"></i>
                <span>{{ __('feed.official_badge', 'Official Update') }}</span>
              </span>
            </template>
            <template x-if="post.is_historian || post.id === '{{ \App\services\FamilyHistorianService::HISTORIAN_ID }}'">
              <span class="badge rounded-pill text-white ms-1.5 px-2 py-0.5 d-inline-flex align-items-center gap-1 shadow-2xs" style="background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); color: #ffffff !important; font-size: 0.72rem; font-weight: 600; white-space: nowrap; flex-shrink: 0;" title="Automated Family Historian Digest">
                <i class="bi bi-journal-richtext" style="color: #fde68a; font-size: 0.75rem;"></i>
                <span>{{ __('feed.historian_badge', 'Do You Know?') }}</span>
              </span>
            </template>
            <template x-if="post.post_tags && post.post_tags.length > 0">
              <span class="text-secondary small fw-normal ms-1">
                <span x-text="window.__ ? window.__('feed.is_with', 'is with') : 'is with'"></span> 
                <template x-for="(tag, tIdx) in getSortedTagsForViewer(post).slice(0, 2)" :key="tag.user_id">
                  <span>
                    <template x-if="isCurrentUser(tag.user_id)">
                      <span class="fw-bold text-primary" x-text="window.__ ? window.__('feed.you', 'you') : 'you'"></span>
                    </template>
                    <template x-if="!isCurrentUser(tag.user_id)">
                      <span class="d-inline-flex align-items-center">
                        <a :href="'/member/profilePage?id=' + tag.user_id" class="fw-semibold text-dark text-decoration-none" style="text-decoration: underline;" x-text="tag.fullName"></a>
                        <template x-if="tag.kinship && tag.kinship !== 'Family Member' && tag.kinship !== 'Relative' && tag.kinship !== 'You'">
                          <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5 ms-0.5" style="font-size: 0.75rem; font-weight: 600;" x-text="tag.kinship"></span>
                        </template>
                      </span>
                    </template>
                    <span x-show="tIdx === 0 && post.post_tags.length === 2" x-text="' ' + (window.__ ? window.__('feed.and', 'and') : 'and') + ' '"></span>
                    <span x-show="tIdx === 0 && post.post_tags.length > 2">, </span>
                  </span>
                </template>
                <template x-if="post.post_tags.length > 2">
                  <span class="dropdown d-inline">
                    <a href="#" class="fw-semibold text-primary text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" x-text="(post.post_tags.length - 2) + ' ' + (window.__ ? window.__('feed.others', 'others') : 'others')"></a>
                    <ul class="dropdown-menu shadow-sm border-0 p-2" style="border-radius: 12px; min-width: 190px;">
                      <template x-for="tag in getSortedTagsForViewer(post).slice(2)" :key="tag.user_id">
                        <li>
                          <a :href="'/member/profilePage?id=' + tag.user_id" class="dropdown-item d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2">
                            <div class="d-flex align-items-center gap-2">
                              <img :src="tag.profileImg || '/resources/images/profile/avatarM.png'" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                              <span class="small fw-medium" x-text="tag.fullName"></span>
                            </div>
                            <template x-if="tag.kinship && tag.kinship !== 'Family Member' && tag.kinship !== 'Relative' && tag.kinship !== 'You'">
                              <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5 ms-1" style="font-size: 0.75rem; font-weight: 600;" x-text="tag.kinship"></span>
                            </template>
                          </a>
                        </li>
                      </template>
                    </ul>
                  </span>
                </template>
              </span>
            </template>
          </div>
          <div class="d-flex align-items-center gap-2 mt-0.5 flex-wrap">
            <small class="text-muted" style="font-size: 0.85rem;" x-text="formatDate(post.post_time || post.date_created)"></small>
            <template x-if="post.is_official || post.id === '{{ \App\services\FamilyPlatformAccountService::getAccountId() }}'">
              <span class="badge bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-pill" style="font-size: 0.78rem; font-weight: 600; padding: 2px 8px;">
                ✨ {{ __('feed.platform_announcement', 'Platform Announcement') }}
              </span>
            </template>
            <template x-if="!(post.id === 'FAMILY_PLATFORM_OFFICIAL' || post.is_official) && post.postFamCode">
              <span class="badge rounded-pill" 
                    :class="post.target_scope === 'birth' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : (post.target_scope === 'married' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis')" 
                    :style="post.target_scope === 'birth' ? 'color: #7f1d1d !important; background-color: #fee2e2 !important; border-color: #fca5a5 !important;' : (post.target_scope === 'married' ? 'color: #14532d !important; background-color: #dcfce7 !important; border-color: #86efac !important;' : '')"
                    style="font-size: 0.78rem; font-weight: normal; padding: 2px 8px; white-space: nowrap; flex-shrink: 0;">
                <i class="bi me-1" :class="post.target_scope === 'birth' ? 'bi-heart-pulse-fill' : (post.target_scope === 'married' ? 'bi-house-heart-fill' : 'bi-people')"></i>
                <span x-text="'#' + String(post.postFamCode).replace(/^#+/, '')"></span>
              </span>
            </template>
            <template x-if="!(post.id === 'FAMILY_PLATFORM_OFFICIAL' || post.is_official) && !post.postFamCode && !isOwnPost(post)">
              <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis"
                    style="font-size: 0.78rem; font-weight: normal; padding: 2px 8px; white-space: nowrap; flex-shrink: 0;">
                <i class="bi bi-people me-1"></i>
                <span>Connected Kin</span>
              </span>
            </template>
          </div>
        </div>
        <div class="dropdown">
          <button class="btn btn-sm btn-link text-muted p-1 rounded-circle d-inline-flex align-items-center justify-content-center" type="button" :id="'kebab' + post.post_no" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; width: 34px; height: 34px; min-width: 44px; min-height: 44px;" title="Post options" aria-label="Post options">
            <i class="bi bi-three-dots" style="font-size: 1.25rem;"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" :aria-labelledby="'kebab' + post.post_no" style="border-radius: 12px; font-size: 0.9rem; min-width: 170px;">
            <template x-if="isOwnPost(post)">
              <div>
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" style="min-height: 44px;" @click.prevent="cyclePostStage(post, $event)">
                    <i class="bi bi-stars text-warning"></i>
                    <span x-text="post.post_style ? 'Switch 3D Stage Design' : 'Convert to 3D Stage'"></span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" style="min-height: 44px;" @click.prevent="editPost(post)">
                    <i class="bi bi-pencil-square text-primary"></i> {{ __('feed.edit_post', 'Edit Post') }}
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" style="min-height: 44px;" @click.prevent="repostPost(post)">
                    <i class="bi bi-arrow-repeat text-success"></i> {{ __('feed.repost_post', 'Repost') }}
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" style="min-height: 44px;" @click.prevent.stop="sharePost(post)">
                    <i class="bi bi-send-fill text-success" style="transform: rotate(-15deg); display: inline-block;"></i> {{ __('feed.share_post', 'Share Post') }}
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" href="#" style="min-height: 44px;" @click.prevent="deletePost(post.post_no)">
                    <i class="bi bi-trash3"></i> {{ __('feed.delete_post', 'Delete Post') }}
                  </a>
                </li>
              </div>
            </template>
            <template x-if="!isOwnPost(post)">
              <div>
                <template x-if="post.id && post.id !== 'FAM_PLATFORM_OFFICIAL' && post.id !== 'FAMILY_HISTORIAN'">
                  <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" style="min-height: 44px; white-space: nowrap;" @click.prevent="restrictPostAuthor(post)">
                      <i class="bi bi-person-slash text-warning"></i> {{ __('feed.restrict_member', 'Restrict Member') }}
                    </a>
                  </li>
                </template>
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" href="#" style="min-height: 44px; white-space: nowrap;" @click.prevent="openReportModal('post', post.post_no)">
                    <i class="bi bi-flag-fill"></i> {{ __('feed.report_post', 'Report Post') }}
                  </a>
                </li>
              </div>
            </template>
            <template x-if="isUserTaggedInPost(post)">
              <li>
                <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-warning" href="#" style="min-height: 44px;" @click.prevent="untagSelfFromPost(post)">
                  <i class="bi bi-tag-fill"></i> {{ __('feed.remove_tag', 'Remove Tag from Post') }}
                </a>
              </li>
            </template>
            <!-- Optional On-Demand Translation Trigger (Only when post has translatable caption text) -->
            <template x-if="getMediaCaption(post)">
              <li>
                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" style="min-height: 44px;" @click.prevent="window.toggleTranslatePost ? window.toggleTranslatePost(post) : null">
                  <i class="bi bi-translate text-primary"></i>
                  <span x-text="post._isTranslated ? (window.__ ? window.__('feed.see_original', 'See original') : 'See original') : (window.__ ? window.__('feed.translate_post', 'Translate post') : 'Translate post')"></span>
                </a>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </div>
  </div>
