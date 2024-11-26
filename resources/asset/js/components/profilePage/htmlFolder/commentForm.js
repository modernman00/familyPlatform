export const commentForm = (data) => {
  const { post_no } = data
  return ` <p id="formComment${post_no}_notification"></p>

  <form 
    action="/postCommentProfile" 
    method="post" id="formComment${post_no}" 
    style="display:none" 
    enctype="multipart/form-data">

    <input 
      name='post_no' 
      type="hidden" 
      name="${post_no}" 
      value=${post_no} />

    <input 
      class="w3-input w3-border w3-round-large inputComment" 
      type="text" 
      placeholder="Write a comment"
      id="inputComment${post_no}" 
      value = "" name='comment'>

    <br>

    <button 
      type='submit' 
      id="submitComment${post_no}" 
      class="w3-button w3-green submitComment">
        Submit
    </button>
    
    <br><br>
  </form>`
}
