import { nameImgTiming } from './htmlFolder/nameImageTiming'
import { commentForm } from "./htmlFolder/commentForm"
import { likeCommentButton } from "./htmlFolder/likeCommentButton"
import { showPostImg, imgCount } from "./htmlFolder/showPostImages"
import { showComment} from "./comment"


export const html = (el, comment = null) => {
  const { post_no, postMessage } = el
  return `
    <div class="post card" id="post${post_no}">
     <div class="card-body post${post_no}" id="postIt">
    ${nameImgTiming(el)}

    <div class="post-content">
    <p class="card-text"> ${postMessage} </p>

     <div class="photo-grid grid-${imgCount(el)}">
      ${showPostImg(el)}
    </div>
    </div>

    ${likeCommentButton(el)}
    ${commentForm(el)}
    <div id = 'showComment${post_no}' class="comment-section">
    ${showComment(comment)}

      
    </div>
`
}




