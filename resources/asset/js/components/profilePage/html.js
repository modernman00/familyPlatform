import { nameImgTiming } from './htmlFolder/nameImageTiming'
import { commentForm } from "./htmlFolder/commentForm"
import { likeCommentButton } from "./htmlFolder/likeCommentButton"
import { showPostImg } from "./htmlFolder/showPostImages"
import { showComment } from "./comment"
import { renderPoll, renderReactions } from "./htmlFolder/engagementHtml"
import { extractVideoFromText, cleanPostMessage } from "./videoParser"
import { esc } from "../global"

const renderVideoPlayer = (postMessage) => {
  const video = extractVideoFromText(postMessage);
  if (!video) return '';

  const isShort = Boolean(video.isShort || video.type === 'tiktok' || (video.originalUrl && video.originalUrl.includes('/shorts/')));

  if (video.type === 'youtube' || video.type === 'vimeo' || video.type === 'cloudflare' || video.type === 'tiktok') {
    return `
      <div class="video-embed-container feed-video-breakout mb-3" style="border-radius: 14px; overflow: hidden; width: 100%; max-width: 100%; margin: 0 auto; background: #07090e; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div class="video-rendition-frame" style="width: 100%; max-width: ${isShort ? 'min(460px, 100%)' : '100%'}; aspect-ratio: ${isShort ? '9 / 16' : '16 / 9'}; max-height: ${isShort ? 'min(820px, 84vh)' : 'min(720px, 80vh)'}; margin: 0 auto;">
          <iframe src="${esc(video.embedUrl)}"
                  title="Video Player"
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                  allowfullscreen 
                  loading="lazy"
                  style="width: 100%; height: 100%; border: none; border-radius: 14px; background: #07090e; display: block;"></iframe>
        </div>
      </div>
    `;
  } else if (video.type === 'direct') {
    return `
      <div class="video-embed-container feed-video-breakout mb-3 position-relative" style="border-radius: 14px; overflow: hidden; background: #07090e; box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
        <div class="video-rendition-frame position-relative overflow-hidden" 
             style="width: 100%; max-width: 100%; aspect-ratio: 4 / 5; max-height: 880px; border-radius: 14px; background: radial-gradient(circle at center, rgba(30, 41, 59, 0.55) 0%, rgba(7, 9, 14, 0.98) 100%);">
          <video referrerpolicy="no-referrer" class="feed-post-video w-100 h-100" 
                 src="${esc(video.embedUrl)}" 
                 controls 
                 playsinline 
                 webkit-playsinline 
                 muted 
                 loop 
                 preload="metadata" 
                 onloadedmetadata="
                   if (this.videoWidth && this.videoHeight) {
                     const r = this.videoWidth / this.videoHeight;
                     const frame = this.parentElement;
                     if (r < 0.88) {
                       frame.style.aspectRatio = '4 / 5';
                       frame.style.maxHeight = '880px';
                       this.style.objectPosition = '50% 28%';
                     } else if (r > 1.15) {
                       frame.style.aspectRatio = r > 1.6 ? '16 / 10' : (r.toFixed(4) + ' / 1');
                       frame.style.maxHeight = 'min(680px, 75vh)';
                       this.style.objectPosition = '50% 50%';
                     } else {
                       frame.style.aspectRatio = r.toFixed(4) + ' / 1';
                       frame.style.maxHeight = 'min(720px, 78vh)';
                       this.style.objectPosition = '50% 50%';
                     }
                     frame.style.maxWidth = '100%';
                   }
                 "
                 style="width: 100%; height: 100%; object-fit: cover; background: transparent; border-radius: 14px; display: block;"></video>
        </div>
      </div>
    `;
  }
  return '';
};

export const html = (el, comment = null) => {
  const { post_no, postMessage } = el
  const video = extractVideoFromText(postMessage)
  const displayMsg = cleanPostMessage(postMessage, video)

  return `<div class="w3-container w3-card w3-white w3-round w3-margin post${post_no}"><br>

      ${nameImgTiming(el)}

    <hr class="w3-clear">

    ${displayMsg ? `<p class="postFont"> ${esc(displayMsg)} </p>` : ''}

     ${renderVideoPlayer(postMessage)}

     ${showPostImg(el)}
     
     ${renderPoll(el?.poll)}
     ${renderReactions(el)}

    ${likeCommentButton(el)}

    ${commentForm(el)}

    <div id = 'showComment${post_no}'>

      ${showComment(comment)}
      
    </div><br>
  </div>`
}




