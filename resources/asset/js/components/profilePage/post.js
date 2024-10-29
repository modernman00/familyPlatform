import { html } from "./html"
import { id, log, showError } from '../global'


const famCode = localStorage.getItem('requesterFamCode');
/**
 * Renders a post and its associated comments in the DOM.
 * 
 * This function takes a post object and an array of comment data,
 * filters the comments to include only those associated with the 
 * given post, generates HTML for the post using the `html` function, 
 * and appends it to the 'postIt' container in the DOM.
 *
 * @param {Object} el - The post object containing post data, including post number.
 * @param {Array} commentData - An array of comment objects associated with posts.
 * @returns {boolean} - Returns false if the post object is invalid.
 */
export const allPost = (postData, commentData) => {

  let postNo = parseInt(postData.post_no)

  let postFamCode =postData.postFamCode

  const filterComment = commentData.filter(comm => parseInt(comm.post_no) === postNo ) // filter the comment to an array
  const postHtml = html(postData, filterComment)
  if(postFamCode === famCode) {
    id('postIt').insertAdjacentHTML('beforeend', postHtml)
  }
  
  
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

  if (!el) { return false; }

  const commentForm1 = id(`formComment${el.post_no}`);
  const inputComment = id(`inputComment${el.post_no}`)
  const submitComment = id(`submitComment${el.post_no}`);

  if (!commentForm1 || !inputComment || !submitComment) {

    const appendHTML = html(el);

  
    if (el.postFamCode === famCode) {

      log("from family")

      id('postIt').insertAdjacentHTML('afterbegin', appendHTML)

    }

  }

}
