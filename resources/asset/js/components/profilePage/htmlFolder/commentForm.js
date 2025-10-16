export const commentForm = ({ post_no }) => {
  return `
    <p id="formComment${post_no}_notification"></p>

    <form 
      action="/postCommentProfile" 
      method="post" 
      id="formComment${post_no}" 
      enctype="multipart/form-data"
      class="mb-4 d-none formComment${post_no}"
    >

      <input 
        type="hidden" 
        name="post_no" 
        value="${post_no}" 
      />

       <div id="emojiCommentPickerList${post_no}" class="d-flex flex-wrap gap-2 mt-2 d-none" role="listbox" aria-hidden="true">
      </div>

      <div id="gifPickerList" class="d-flex flex-wrap gap-2 mt-2 d-none" role="listbox" aria-hidden="true"></div>

        

             

                <textarea class="form-control inputComment mb-3" data-commentEmoji-target id="inputComment${post_no}" name="comment" rows=2> </textarea/>
                
     <div class="mt-3 d-flex justify-content-between align-items-center position-relative">
     <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                title="add emoji" 
                                id="emojiToggle${post_no}"aria-expanded="false" aria-controls="emojiPickerList">
                                😊
                            </button>


              <button type="button" class="btn btn-sm btn-outline-secondary" id="stickerToggle${post_no}"  title="Stickers"
                                aria-label="Stickers">🏷️</button>
  </div>
                <button 
                    type="submit" 
                    id="submitComment${post_no}" 
                    class="btn btn-outline-primary btn-sm submitComment"
                >
              Submit
            </button>
      
            
          </div>
    </form>
  `;
};
