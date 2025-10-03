import { format } from "timeago.js"
import { toSentenceCase, id} from "@shared"

export const commentHTML = (data) => {

  const {profileImg, fullName, date_created, img, comment, comment_no} = data

  const imgURL = profileImg || img 

  
  const image = (imgURL) ? `/public/img/profile/${imgURL}` : `/public/avatar/avatarF.png`

  return `<div class="d-flex mb-3 commentDiv align-items-start" id="comment${comment_no}" name="commentDiv">
  <img src="${image}" alt="Avatar" class="rounded-circle me-2 commentImg" width="32" height="32">

  <div class="flex-grow-1">
    <div class="d-flex justify-content-between align-items-center">
      <strong>${toSentenceCase(fullName)}</strong>
      <small class="text-muted commentTiming" datetime="${date_created}" title="${date_created}">
        ${format(date_created)}
      </small>
    </div>

    <div class="comment-text mb-2">
      ${comment}
    </div>

    <div class="comment-actions d-flex gap-3">
      <button class="btn btn-sm btn-icon text-danger" onclick="removeComment(${comment_no})" title="Remove">
        <i class="bi bi-trash"></i>
      </button>
      <button class="btn btn-sm btn-icon text-warning" onclick="reportComment(${comment_no})" title="Report">
        <i class="bi bi-flag"></i>
      </button>
      <button class="btn btn-sm btn-icon text-primary" onclick="likeComment(${comment_no})" title="Like">
        <i class="bi bi-hand-thumbs-up"></i>
      </button>
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



