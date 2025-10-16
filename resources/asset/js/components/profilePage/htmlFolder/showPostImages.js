export const showPostImg = (data) => {

// GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
  const postImagesWithValues = Object.keys(data)
  .filter(key => key.startsWith('post_img') && data[key] !== null)
  .map(el => data[el])

  const picsImgHtml = (imgElement, i, postNo) => `
  

     
        <img 
          src="/resources/images/post/${imgElement}" 
          alt="images${i}" 
          data-postImgId="${postNo}${imgElement}"
          data-imgIndex="${i}"
          data-postNo="${postNo}"
          class="grid-image zoomable-image${postNo}" 
          id="postImage${i}"
          >
  `;

  const imgElements = postImagesWithValues.map((pics, i) => picsImgHtml(pics, i, data.post_no)).join('');


    // ✅ Optional: return both HTML and count for contributor-safe rendering
  return imgElements

};

export const imgCount = (data) => {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
    const postImagesWithValues = Object.keys(data)
    .filter(key => key.startsWith('post_img') && data[key] !== null)
    .map(el => data[el])
  
    const imageCount = postImagesWithValues.length;
  
      // ✅ Optional: return both HTML and count for contributor-safe rendering
    return imageCount;
  }
