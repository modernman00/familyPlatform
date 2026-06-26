export const likeCommentButton = (data, commentLength) => {
  return `
   <div class="reaction-buttons d-flex justify-content-around border-top border-bottom py-2 mb-2 mt-3 gap-2">
    <button 
      class="btn flex-grow-1 fw-semibold rounded-pill d-flex align-items-center justify-content-center"
      style="background-color: var(--hover-color); color: var(--text-color); border: none; transition: all 0.2s;"
      type="button" 
      id="likeButton${data.post_no}" 
      name="${data.post_no}">
      <i class="bi bi-hand-thumbs-up me-2" style="font-size: 1.1rem; color: var(--text-muted);"></i> 
      Like 
      <span class="badge ms-2" style="background-color: var(--border-color); color: var(--text-color);">
        <span class="likeCounter" id="likeCounter${data.post_no}">${data.post_likes}</span>
      </span>
    </button>

    <button 
      class="btn flex-grow-1 fw-semibold rounded-pill d-flex align-items-center justify-content-center"
      style="background-color: var(--hover-color); color: var(--text-color); border: none; transition: all 0.2s;"
      type="button" 
      id="initComment${data.post_no}">
        <i class="bi bi-chat me-2" style="font-size: 1.1rem; color: var(--text-muted);"></i> 
        Comment 
        <span class="badge ms-2" style="background-color: var(--border-color); color: var(--text-color);">
          <span class="commentCounter" id="commentCounter${data.post_no}">${commentLength}</span>
        </span>
    </button>
   
    </div>
    `
}