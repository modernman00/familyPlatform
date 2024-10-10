import { html } from "./html"
import { id } from '../global'
import { renderMembers } from '../allMembers/api'
import filterMembersByFamCode from '../allMembers/filterMembersByFamCode'



export const allPost = (el, commentData) => {

  if (!el) { return false; }

  let postNo = parseInt(el.post_no)

  const filterComment = commentData.filter(comm => postNo === parseInt(comm.post_no)) // filter the comment to an array

  const postHtml = html(el, filterComment)

  id('postIt').insertAdjacentHTML('beforeend', postHtml)
}




export const appendNewPost = (el) => {

  if (!el) { return false; }

  const commentForm1 = id(`formComment${el.post_no}`);
  const inputComment = id(`formComment${el.post_no}`)
  const submitComment = id(`formComment${el.post_no}`);

  if (!commentForm1 || !inputComment || !submitComment) {

    const appendHTML = html(el);

    id('postIt').insertAdjacentHTML('afterbegin', appendHTML)

  }

}
