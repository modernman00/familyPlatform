export const likeCommentButton = (data) => {
  return `
   <div class="reaction-buttons d-flex justify-content-between border-top border-bottom py-2 mb-3">
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
      </button>
        <button><i class="bi bi-share me-1"></i> Share</button>
    </div>
    `
}