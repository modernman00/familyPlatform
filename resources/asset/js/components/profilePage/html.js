import { nameImgTiming } from './htmlFolder/nameImageTiming'
import { commentForm } from "./htmlFolder/commentForm"
import { likeCommentButton } from "./htmlFolder/likeCommentButton"
import { showPostImg } from "./htmlFolder/showPostImages"
import { showComment, writeComment } from "./comment"


export const html = (el, comment = null) => {
  const { post_no, postMessage } = el
  return `
    <div class="post card">
     <div class="card-body post{{$data['post_no']}}" id="postIt">
    ${nameImgTiming(el)}
    <p class="card-text"> ${postMessage} </p>
    ${showPostImg(el)}
    ${likeCommentButton(el)}
    ${commentForm(el)}
    <div id = 'showComment${post_no}' class="comment-section">
    ${showComment(comment)}
    ${writeComment(el)}
      
    </div>
`
}




