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

  const isShort = (video.originalUrl && video.originalUrl.includes('/shorts/'));

  if (video.type === 'youtube' || video.type === 'vimeo' || video.type === 'cloudflare') {
    return `
      <div class="video-embed-container mb-3" style="border-radius: 14px; overflow: hidden; max-height: ${isShort ? '560px' : '440px'}; max-width: ${isShort ? '380px' : '100%'}; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div class="ratio ${isShort ? 'ratio-9x16' : 'ratio-16x9'}">
          <iframe src="${esc(video.embedUrl)}"
                  title="Video Player"
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                  allowfullscreen 
                  loading="lazy"
                  style="width: 100%; height: 100%; border: none; border-radius: 14px; background: #07090e;"></iframe>
        </div>
      </div>
    `;
  } else if (video.type === 'direct') {
    return `
      <div class="video-embed-container mb-3 position-relative" style="border-radius: 14px; overflow: hidden; background: #07090e; box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
        <div class="video-rendition-frame position-relative mx-auto overflow-hidden" 
             style="border-radius: 14px; background: radial-gradient(circle at center, rgba(30, 41, 59, 0.55) 0%, rgba(7, 9, 14, 0.98) 100%);">
          <video class="feed-post-video w-100" 
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
                     if (r < 0.88) {
                       this.style.maxHeight = '580px';
                       this.parentElement.style.maxWidth = r <= 0.65 ? '400px' : '460px';
                     } else {
                       this.style.maxHeight = '440px';
                       this.parentElement.style.maxWidth = '100%';
                     }
                   }
                 "
                 style="width: 100%; max-height: 480px; object-fit: contain; background: transparent; border-radius: 14px; display: block; margin: 0 auto;"></video>
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




