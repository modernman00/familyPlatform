export const likeCommentButton = (data, commentLength) => {
  return `
   <div class="reaction-buttons d-flex justify-content-between border-top border-bottom py-2 mb-1">
    <button 
      type="button" 
      id="likeButton${data.post_no}" 
      name="${data.post_no}"
      <i class="bi bi-hand-thumbs-up me-1"></i> 
          Like 
        <b>
          <span class="likeCounter" id="likeCounter${data.post_no}">
            ${data.post_likes}
          </span>
        </b>
    </button>

    <button 
      type="button" 
      id="initComment${data.post_no}">
        <i class="bi bi-chat me-1"></i> 
          Comment 
          (<span class="commentCounter" id="commentCounter${data.post_no}">
            ${commentLength}
          </span>)
          
      </button>
   
    </div>
    `
}