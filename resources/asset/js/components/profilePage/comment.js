import { format, render } from "timeago.js"
import { toSentenceCase, id } from "@shared"
import { log } from "../global";
import { renderTopReactions } from './showEmojiOnComment.js'

const reqId = localStorage.getItem('requesterId');

export const commentHTML = (data, postId) => {

  const { profileImg, fullName, date_created, img, comment, comment_no, id } = data
  const imgURL = profileImg || img
  const image = (imgURL) ? `/resources/images/profile/${imgURL}` : `/public/avatar/avatarF.png`
  let counts = {}
  let total;


  // check if reactions and counts exist
  if (data.reactions) {
    counts = data?.reactions?.counts ?? {};
    total = data?.reactions?.counts?.totalReactions;
  } else {
    counts = {};
    total = 0;
  }


  return `<div class="d-flex mb-3 commentDiv align-items-start" data-commentDiv-no="${comment_no}" id="commentDiv${comment_no}" name="commentDiv">

  <img src="${image}" alt="Avatar" class="rounded-circle me-2 commentImg" width="32" height="32">

  <div class="flex-grow-1">
    <div class="d-flex justify-content-between align-items-center">
      <strong>${toSentenceCase(fullName)}${comment_no}</strong>
      <small class="text-muted commentTiming" datetime="${date_created}" title="${date_created}">
        ${format(date_created)}
      </small>
    </div>

    <div class="comment-text mb-2">
      ${comment}
    </div>

      <div class="d-flex reaction-preview-section align-items-center mb-2 gap-2"> 

        <div class="reaction-preview" id="reaction-preview-${comment_no}">
        ${renderTopReactions(counts, comment_no)}
        </div>

         <div class="reaction-summary" data-comment-no="${comment_no}" role="tooltip" id="reaction-summary-${comment_no}" style="display:none;">
        </div>

      </div>

      <div class="comment-actions d-flex gap-3">         
                <div class="reaction-bar"  id="reaction-bar-${comment_no}">

                    <div class="reaction-option" data-option-no="${comment_no}" aria-label="Like" id="reaction-option-like-${comment_no}" data-reaction="like" data-label="likes"> 👍 </div>
                    <div class="reaction-option" data-option-no="${comment_no}" aria-label="Love" id="reaction-option-love-${comment_no}" data-reaction="love" data-label="love">❤️</div>
                    <div class="reaction-option" data-option-no="${comment_no}" aria-label="Haha" id="reaction-option-haha-${comment_no}" data-reaction="haha" data-label="haha">😄</div>
                    <div class="reaction-option" data-option-no="${comment_no}" aria-label="Wow" id="reaction-option-wow-${comment_no}" data-reaction="wow" data-label="wow">😮</div>
                    <div class="reaction-option" data-option-no="${comment_no}" aria-label="Sad" id="reaction-option-sad-${comment_no}" data-reaction="sad" data-label="sad">😢</div>
                    <div class="reaction-option" data-option-no="${comment_no}" aria-label="Angry" id="reaction-option-angry-${comment_no}"
                     data-reaction="angry" data-label="angry">😠</div>
                </div>

                <div class="reaction-button like-button-${comment_no}" id="like-button-${comment_no}" data-comment-no="${comment_no}">
                    <i class="bi bi-hand-thumbs-up reaction-icon" id="like-icon-${comment_no}"></i>
                    <span>Like</span>
                     <div class="reaction-count" id="like-count-${comment_no}">${total}</div>
                   
                </div>

                ${reqId == id || reqId == postId ?
      `<button class="btn btn-sm btn-icon text-danger" id="removeComment(${comment_no})" title="Remove">
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
  }
  // only run if there is comment
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

  const commentHtml = commentHTML(commentData)
  commentContainer.insertAdjacentHTML('beforeend', commentHtml);
}






