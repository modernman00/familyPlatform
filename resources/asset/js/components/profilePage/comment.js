import { format } from "timeago.js"
import { id, log } from "../global";

export const commentHTML = (data) => {

  const imgURL = (data.img) ? data.img : data.profileImg

  const img = (imgURL) ? `/public/img/profile/${imgURL}` : `/public/avatar/avatarF.png`

  return `<div class='w3-ul w3-border w3-round' id='comment${data.comment_no}' name='commentDiv'>
            <div class='w3-container commentDiv'>
              <img src='${img}' alt='Avatar' class='w3-left w3-circle w3-margin-right commentImg' style='width:50px; height:50px'>
              <p class='w3-right w3-opacity commentTiming' datetime='${data.date_created}' title='${data.date_created}'> ${format(data.date_created)} </p> 
              <p class='commentFont'> ${data.comment}</p>
            </div>
          </div>`
}

export const showComment = (comment) => {
  if (!comment) {
    return `<div class="w3-ul w3-border" id="comment" name="commentDiv"></div>`;
  } // only run if there is comment


// USED FOR ALL THE COMMENTS WHEN THE PAGE IS LOADING
  const commentHTMLArray = comment.map(commentElement => {
    return commentHTML(commentElement);
  });

  return commentHTMLArray.join(''); // Join the array elements into a single string
}


export const appendNewComment = (commentData) => {
        log('comment data', commentData)
        const idDiv = `showComment${commentData.post_no}`
        const commentContainer = id(idDiv);
        const commentHtml = commentHTML(commentData)

         if(commentContainer) {
          commentContainer.insertAdjacentHTML('beforeend', commentHtml)
        } else {
          console.warn('comment container does not exist' + idDiv)
        }
   
}


