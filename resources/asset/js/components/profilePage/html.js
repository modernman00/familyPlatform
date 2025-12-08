import { nameImgTiming } from './htmlFolder/nameImageTiming'
import { commentForm } from "./htmlFolder/commentForm"
import { likeCommentButton } from "./htmlFolder/likeCommentButton"
import { showPostImg, imgCount } from "./htmlFolder/showPostImages"
import { showComment} from "./comment"


export const html = (postArray, comment = []) => {
  const { post_no, postMessage, id } = postArray

  return `
    <div class="post card" id="post${post_no}">
     <div class="card-body post${post_no}" id="postIt">
    ${nameImgTiming(postArray)}

    <div class="post-content">
    <p class="card-text"> ${postMessage} </p>

     <div class="photo-grid grid-${imgCount(postArray)}">
      ${showPostImg(postArray)}
    </div>
    </div>

    ${likeCommentButton(postArray, comment.length)}
    ${commentForm(postArray)}
    <div id = 'showComment${post_no}' class="comment-section">
    ${showComment(comment, id)}

      
    </div>
`
}




