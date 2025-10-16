import { format } from "timeago.js"
import { toSentenceCase, id} from "@shared"

const reqId = localStorage.getItem('requesterId');

export const commentHTML = (data, postId) => {

  const {profileImg, fullName, date_created, img, comment, comment_no, id} = data
  const imgURL = profileImg || img 
  const image = (imgURL) ? `/resources/images/profile/${imgURL}` : `/public/avatar/avatarF.png`

  return `<div class="d-flex mb-3 commentDiv align-items-start" data-commentDiv-no="${comment_no}" id="commentDiv${comment_no}" name="commentDiv">

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

     <div class="reaction-preview" id="reaction-preview-${comment_no}"></div>

      <div class="comment-actions d-flex gap-3">
    
                
                <div class="reaction-bar" id="reaction-bar-${comment_no}">
                    <div class="reaction-option" data-reaction="like" data-label="Like">
                        <div class="reaction-emoji">👍</div>
                    </div>
                    <div class="reaction-option" data-reaction="love" data-label="Love">
                        <div class="reaction-emoji">❤️</div>
                    </div>
                    <div class="reaction-option" data-reaction="haha" data-label="Haha">
                        <div class="reaction-emoji">😄</div>
                    </div>
                    <div class="reaction-option" data-reaction="wow" data-label="Wow">
                        <div class="reaction-emoji">😮</div>
                    </div>
                    <div class="reaction-option" data-reaction="sad" data-label="Sad">
                        <div class="reaction-emoji">😢</div>
                    </div>
                    <div class="reaction-option" data-reaction="angry" data-label="Angry">
                        <div class="reaction-emoji">😠</div>
                    </div>
                </div>

                <div class="reaction-button like-button-${comment_no}" id="like-button-${comment_no}" data-comment-no="${comment_no}">
                    <i class="bi bi-hand-thumbs-up reaction-icon"></i>
                    <span>Like</span>
                    <div class="reaction-count" id="like-count-${comment_no}">0</div>
                </div>

                ${
                  reqId == id || reqId == postId ? `<button class="btn btn-sm btn-icon text-danger" id="removeComment(${comment_no})" title="Remove">
                    <i class="bi bi-trash" id="removeCommentIcon${comment_no}"></i>
                </button>` : ''
                }
                
         
      </div>




  </div>
</div><hr>`

}

// i need the postid to use to show the delete button 
export const showComment = (comment, postId) => {
  if (!comment) {
    return `<div id="comment" name="commentDiv"></div>`;
  } // only run if there is comment


  // USED FOR ALL THE COMMENTS WHEN THE PAGE IS LOADING
  const commentHTMLArray = comment.map(commentElement => {
    return commentHTML(commentElement, postId);
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



