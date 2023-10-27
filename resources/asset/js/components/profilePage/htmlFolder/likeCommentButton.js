export const likeCommentButton = (data) => {
  return `
  <button type="button" id="likeButton${data.post_no}" name="${data.post_no}"
    class="w3-button w3-tiny w3-green w3-margin-bottom">
    <em class="fa fa-thumbs-up"></em>
     Like <b><span class="likeCounter" id="likeCounter${data.post_no}">${data.post_likes}</span></b>
  </button>

   <button type="button" id="initComment${data.post_no}"
    class="w3-button w3-tiny w3-theme-d2 w3-margin-bottom"><em class="fa fa-comment"></em> Comment </button>
    `
}