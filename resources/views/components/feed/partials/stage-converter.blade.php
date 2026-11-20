{{-- @props(["post" => null]) --}}
      <!-- 3. Retroactive 1-Tap In-Feed 3D Stage Converter Bar (Author Only, Text/Stage Posts) -->
      <template x-if="isOwnPost(post) && (getMediaCaption(post) || post.ar_emoji || getPostStageMeta(post))">
        <div class="fp-stage-converter mt-2.5" data-testid="infeed-stage-converter">
          <div class="fp-stage-converter__row">
            <button type="button"
                    class="fp-stage-converter__trigger"
                    :disabled="!!convertingStagePosts[post.post_no]"
                    @click.stop="cyclePostStage(post, $event)"
                    title="One-tap convert this post into a 3D futuristic stage">
              <template x-if="convertingStagePosts[post.post_no]">
                <span class="spinner-border spinner-border-sm" role="status" style="width: 13px; height: 13px; border-width: 2px;"></span>
              </template>
              <template x-if="!convertingStagePosts[post.post_no]">
                <i class="bi bi-magic"></i>
              </template>
              <span x-text="post.post_style ? '✨ Next 3D Stage' : '✨ Convert to 3D Stage'"></span>
            </button>

            <template x-for="st in textStageCatalog" :key="st.slug">
              <button type="button"
                      class="fp-stage-converter__chip"
                      :class="post.post_style === st.slug ? 'is-active' : ''"
                      :disabled="!!convertingStagePosts[post.post_no]"
                      @click.stop="setPostStage(post, st.slug, $event)"
                      :title="'Convert post to ' + st.label">
                <span x-text="st.label"></span>
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
