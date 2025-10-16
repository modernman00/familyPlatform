import { id, log, qSel, qSelAll } from '@shared'
import axios from 'axios';

  // const modal = id("imageModal");
  // const modalImg = id("modalImage");
  // const close = qSel(".close");

  // qSelAll(".photo-grid img").forEach(img => {
  //   img.addEventListener("click", () => {
  //     modal.style.display = "block";
  //     modalImg.src = img.src;
  //   });

  //   // Touch support for mobile
  //   img.addEventListener("touchstart", () => {
  //     modal.style.display = "block";
  //     modalImg.src = img.src;
  //   });
  // });

  // close.onclick = () => {
  //   modal.style.display = "none";
  // };

  // modal.onclick = () => {
  //   modal.style.display = "none";
  // };


  // Like functionality

const likeCount = async( e) => {
  const elementId = e.target.id;
  
  if (elementId.startsWith('likeBtn')) {
     // remove likeBtn from the id 
     const newId = elementId.replace('likeBtn', '')

     // remove the love symbol from the idInnerHTML
     // include /\n/g in the replace function 
     
   
    let likeCounter = id(elementId).innerHTML.replace('❤️', '').trim()
    // make likeCounter a number and add 1 
    likeCounter = parseInt(likeCounter) + 1

    // update the column like in the database table memory 

    await axios.put(`/memory/like/${newId}/${likeCounter}`)
        .then(res => {
          if (res.data.token == 'success') {
             log(res.data.message)
             id(elementId).innerHTML = `❤️ ${res.data.message} `
             log(res.data.message)
          }
        })
        .catch(err => console.error('Like error:', err));

   
  }
   
}

document.addEventListener('click', likeCount);

const processMemory = (event) => {

  event.preventDefault();

  postFormData('/memory', 'memoryUploadForm', 'memory')

  const grid = id('photoGrid')

}

id('submitMemoryBtn').addEventListener('click', processMemory);