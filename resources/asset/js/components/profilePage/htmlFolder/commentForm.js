export const commentForm = ({ post_no }) => {
  return `
    <p id="formComment${post_no}_notification"></p>

    <form 
      action="/postCommentProfile" 
      method="post" 
      id="formComment${post_no}" 
      style="display:none" 
      enctype="multipart/form-data"
      class="mb-4"
    >

      <input 
        type="hidden" 
        name="post_no" 
        value="${post_no}" 
      />

      <input 
        type="text" 
        class="form-control form-control-sm rounded-pill inputComment" 
        placeholder="Write a comment" 
        id="inputComment${post_no}" 
        name="comment" 
        value=""
      />

      <div class="mt-3">
        <button 
          type="submit" 
          id="submitComment${post_no}" 
          class="btn btn-success btn-sm submitComment"
        >
          Submit
        </button>
      </div>
    </form>
  `;
};
