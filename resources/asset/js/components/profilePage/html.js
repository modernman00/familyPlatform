import { removeDiv, createAndAppendElement } from '../helper/general'

export const allPost = (el) => {
   
    if (el) {

        let imgArr = []
        let x;

        for (x = 0; el.post_img; x++) {
            imgArr.push(el.post_img[x])
        }



        const img = `/img/profile/${el.img}` ?? "/avatar/avatarF.png"

        const html = `<div class="w3-container w3-card w3-white w3-round w3-margin"><br>
        <a href="/profilepage/img?dir=img&pics=${el.img}&pID=${el.post_no}&path=profile">
        <img src=${img} alt="img" class="w3-left w3-circle w3-margin-right" style="width:60px">
        </a>
        <span class="w3-right w3-opacity"> ${el.timing} ago</span>

  <h5 id="fullName"> ${el.fullName}
  </h5>


  <hr class="w3-clear">

  <p class="postFont"> ${el.postMessage} </p>

  <div class="w3-row-padding" style="margin:0 -16px">
  ${imgArr.map((image, i) => {
            return `<a href=/profilepage/img?dir=img&pics=image&pID=${image.post_no}&path=post> <div class=w3-half> <img src=/img/post/${image.post_no} style=width:100% alt=images class=w3-margin-bottom w3-hover-sepia id=postImage${i}></div></a>`
        })}

    <br>
  </div>

  <button type="button" id="likeButton${el.post_no}" name="${el.post_no}"
    class="w3-button w3-tiny w3-green w3-margin-bottom">
    <em class="fa fa-thumbs-up"></em>
     Like <b><span class="likeCounter" id="likeCounter${el.post_no}">${el.post_likes}</span></b>
  </button>

  <button type="button" id="initComment${el.post_no}"
    class="w3-button w3-tiny w3-theme-d2 w3-margin-bottom"><em class="fa fa-comment"></em> Comment </button>

  <p id="formComment${el.post_no}_notification"></p>

  <form action="/postCommentProfile" method="post" id="formComment${el.post_no}" style="display:none" enctype="multipart/form-data">

    <input name='post_no' type="hidden" name="${el.post_no}" value=${el.post_no} />

    <input class="w3-input w3-border w3-round-large inputComment" type="text" placeholder="Write a comment"
      id="inputComment${el.post_no} " name='comment'>

    <br>

    <button type='submit' id="submitComment${el.post_no}" class="w3-button w3-green submitComment">Submit</button>
  </form>

  <br><br>

  
`
        createAndAppendElement('div', 'postIt', 'messagePost', 'postIt')

        document.getElementById('postIt').insertAdjacentHTML('beforeend', html)

    } else {

        return `<p> Sorry, we could find the data</p>`

    }
}