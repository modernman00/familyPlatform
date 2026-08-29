import { nameImgTiming } from './htmlFolder/nameImageTiming'
import { commentForm } from "./htmlFolder/commentForm"
import { likeCommentButton } from "./htmlFolder/likeCommentButton"
import { showPostImg } from "./htmlFolder/showPostImages"
import { showComment } from "./comment"
import { renderPoll, renderReactions } from "./htmlFolder/engagementHtml"
import { extractVideoFromText, cleanPostMessage } from "./videoParser"

const renderVideoPlayer = (postMessage) => {
  const video = extractVideoFromText(postMessage);
  if (!video) return '';

  if (video.type === 'youtube' || video.type === 'vimeo' || video.type === 'cloudflare') {
    return `
      <div class="video-embed-container mb-3" style="border-radius: 12px; overflow: hidden; max-height: 420px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div class="ratio ratio-16x9">
          <iframe src="${video.embedUrl}" 
                  title="Video Player" 
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                  allowfullscreen 
                  loading="lazy"
                  style="width: 100%; height: 100%; border: none; border-radius: 12px;"></iframe>
        </div>
      </div>
    `;
  } else if (video.type === 'direct') {
    return `
      <div class="video-embed-container mb-3" style="border-radius: 12px; overflow: hidden; max-height: 420px;">
        <div class="ratio ratio-16x9">
          <video src="${video.embedUrl}" controls preload="metadata" style="width: 100%; height: 100%; object-fit: contain; background: #000; border-radius: 12px;"></video>
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

    ${displayMsg ? `<p class="postFont"> ${displayMsg} </p>` : ''}

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




