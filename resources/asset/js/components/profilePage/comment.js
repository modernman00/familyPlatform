import { format } from "timeago.js"
import { id, log } from "../global";

export const commentHTML = (data) => {

  const {profileImg, fullName, date_created, img, comment, comment_no} = data

  const imgURL = profileImg || img 

  
  const image = (imgURL) ? `/public/img/profile/${imgURL}` : `/public/avatar/avatarF.png`

  return `<div class='d-flex mb-3 commentDiv' id='comment${comment_no}' name='commentDiv'>
         
      <img src='${image}' alt='Avatar' class="rounded-circle me-2 commentImg" width="32" height="32">

              <div class="flex-grow-1">
                <div class="comment">
                  <strong> ${fullName}</strong>
                  ${comment}  
                  <small class='w3-right w3-opacity commentTiming' datetime='${date_created}' title='${date_created}'> ${format(date_created)} 
                  </small> 
                  </div>
              </div>
         
          </div>`
}

export const showComment = (comment) => {
  if (!comment) {
    return `<div id="comment" name="commentDiv"></div>`;
  } // only run if there is comment


  // USED FOR ALL THE COMMENTS WHEN THE PAGE IS LOADING
  const commentHTMLArray = comment.map(commentElement => {
    return commentHTML(commentElement);
  });

  return commentHTMLArray.join(''); // Join the array elements into a single string
}


export const appendNewComment = (commentData) => {

  // check if commentData is valid
  if (!commentData) {
    throw new Error('No comment update received');
  }

  const idDiv = `showComment${commentData.post_no}`
  // check if the div has been created by the DOM 

  const commentContainer = id(idDiv);
    if (!commentContainer) {
    throw new Error(`The comment div id does not exist `)
  }


  const commentHtml = commentHTML(commentData)


  commentContainer.insertAdjacentHTML('beforeend', commentHtml)


}

export const writeComment = (postNo) => {
  return ` 
    <form action="/postCommentProfile" 
      id="formComment${postNo}" 
    >
          <div class="mt-3">
              <div class="input-group">
                <input type="text" class="form-control" 
                id="inputComment${postNo}" placeholder="Write a comment...">

                <button class="btn btn-outline-primary" type="button" id="inputComment${postNo}" name="inputComment">Post</button>

              </div>
          </div>
    </form>
    `
}


