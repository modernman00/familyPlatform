{{-- @props(["post" => null]) --}}
      <!-- 3. Retroactive 1-Tap In-Feed 3D Stage Converter Bar (Author Only, Text/Stage Posts) -->
      <template x-if="isOwnPost(post) && (getMediaCaption(post) || post.ar_emoji || getPostStageMeta(post))">
        <div class="fp-stage-converter mt-2.5" data-testid="infeed-stage-converter">
          <div class="fp-stage-converter__row">
            <button type="button"
                    class="fp-stage-converter__trigger"
                    :disabled="!!convertingStagePosts[post.post_no]"
                    @click.stop="cyclePostStage(post, $event)"
                    title="Cycle through scenic post backdrops and 3D stages">
              <template x-if="convertingStagePosts[post.post_no]">
                <span class="spinner-border spinner-border-sm" role="status" style="width: 13px; height: 13px; border-width: 2px;"></span>
              </template>
              <template x-if="!convertingStagePosts[post.post_no]">
                <i class="bi bi-stars"></i>
              </template>
              <span x-text="post.post_style ? '✨ Next Backdrop' : '✨ Add Backdrop'"></span>
            </button>

            <template x-for="st in textStageCatalog" :key="st.slug">
              <button type="button"
                      class="fp-stage-converter__chip"
                      :class="post.post_style === st.slug ? 'is-active' : ''"
                      :disabled="!!convertingStagePosts[post.post_no]"
                      @click.stop="setPostStage(post, st.slug, $event)"
                      :title="'Apply ' + (st.shortLabel || st.label) + ' post backdrop'">
                <template x-if="st.swatch">
                  <span class="fp-stage-converter__dot" :style="'background:' + st.swatch" aria-hidden="true"></span>
                </template>
                <span x-text="st.shortLabel || st.label"></span>
              </button>
            </template>

            <template x-if="post.post_style">
              <button type="button"
                      class="fp-stage-converter__chip fp-stage-converter__chip--reset"
                      :disabled="!!convertingStagePosts[post.post_no]"
                      @click.stop="setPostStage(post, 'none', $event)"
                      title="Revert to plain text">
                <span>Plain</span>
              </button>
            </template>
          </div>
        </div>
      </template>
