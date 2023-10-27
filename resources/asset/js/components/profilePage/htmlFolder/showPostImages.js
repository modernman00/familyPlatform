export const showPostImg = (data) => {

// GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE

  const postImagesWithValues = Object.keys(data).filter(key => key.startsWith('post_img') && data[key] !== null).map(el => data[el])

  const picsImgHtml = (imgElement, i, postNo) => `
    <a href="/profilepage/img?dir=img&pics=${imgElement}&pID=${postNo}&path=post">
      <div class="w3-half">
        <img src="/img/post/${imgElement}" style="width:100%" alt="images${i}" class="w3-margin-bottom w3-hover-sepia" id="postImage${i}">
      </div>
    </a>
  `;

  const imgElements = postImagesWithValues.map((pics, i) => picsImgHtml(pics, i, data.post_no)).join('');

  return `
    <div class="w3-row-padding" style="margin:0 -16px">
      ${imgElements}
      <br>
    </div>
  `;
};
