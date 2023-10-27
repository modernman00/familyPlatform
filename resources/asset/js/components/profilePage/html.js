import { nameImgTiming } from './htmlFolder/nameImageTiming'
import { commentForm } from "./htmlFolder/commentForm"
import { likeCommentButton } from "./htmlFolder/likeCommentButton"
import { showPostImg } from "./htmlFolder/showPostImages"
import { showComment } from "./comment"


export const html = (el, comment = null) => {
  return `<div class="w3-container w3-card w3-white w3-round w3-margin"><br>

      ${nameImgTiming(el)}

    <hr class="w3-clear">

    <p class="postFont"> ${el.postMessage} </p>

     ${showPostImg(el)}

    ${likeCommentButton(el)}

    ${commentForm(el)}

    <div id = 'showComment${el.post_no}'>

      ${showComment(comment)}
      
    </div><br>
  </div>`
}




