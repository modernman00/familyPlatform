import { html } from "./html"
import { id, log, msgException, showError } from '../global'


const famCode = localStorage.getItem('requesterFamCode');
/**
 * Renders a post and its associated comments in the DOM.
 * * This function now expects a single, complete post object (postData) 
 * which already contains its comments in the 'comments' key, as provided 
 * by the refactored PHP backend.
 *
 * @param {Object} postData - The post object containing post data, and a nested 'comments' array.
 */
export const allPost = (postData) => { // <-- Removed commentData parameter

  // Check for valid post data
  if (!postData || typeof postData.post_no === 'undefined') {
    msgException('Invalid post data structure received.');
    return;
  }

  // 1. The comments array is now directly available inside the postData object.
  // We use the logical OR (|| []) to ensure it's always an array, even if the key is missing.
  const postComments = postData.comments || [];

  // 2. Pass the post data and its now-ready-to-use comment array to the HTML function
  const postHtml = html(postData, postComments);
  
  // 3. Insert the post HTML into the container
  id('postIt').insertAdjacentHTML('beforeend', postHtml);
}


/**
 * Appends a new post to the DOM if it does not already exist.
 * 
 * This function checks for the existence of comment form elements 
 * associated with the provided post object. If any of these elements 
 * are missing, it generates HTML for the post using the `html` function 
 * and inserts it at the beginning of the 'postIt' container.
 *
 * @param {Object} el - The post object containing post data, including post number.
 * @returns {boolean} - Returns false if the post object is invalid.
 */
export const appendNewPost = (el) => {
 const {post_no} = el 
// Generate the IDs for the comment form and its components const 
const commentFormId = `formComment${post_no}`; 
const inputCommentId = `inputComment${post_no}`; 
const submitCommentId = `submitComment${post_no}`;

// Check if the comment form components exist in the DOM
  const commentForm1 = id(commentFormId);
  const inputComment = id(inputCommentId)
  const submitComment = id(submitCommentId);

// If the comment form components do not exist, create and append the new post
  if (!commentForm1 || !inputComment || !submitComment) {
    const appendHTML = html(el);

  // Ensure the post belongs to the correct family code
    // if (el.postFamCode === famCode) {
      id('postIt').insertAdjacentHTML('afterbegin', appendHTML)
    // } else{
    //   return false
    // }

  }

}
