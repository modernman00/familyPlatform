"use strict";
import { id, log } from "../global"
import { showImageFileUploadFn } from "../helper/images"
import  axios from "axios"

// id('profilePics').addEventListener('click', ()=> id('formProfilePics').style.display ="block"

// );

// FOR PROFILE IMAGE CHANGE
// showImageFileUploadFn('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames')

// // FOR POST MODAL IMAGE UPLOAD  

// showImageFileUploadFn('uploadButton', 'post_img', 'postModalImgFileNames')


// id('submitProfilePics').addEventListener('click', ()=> {23

//   // Get the form element
//     const form = document.getElementById("formProfilePics");

//     // Create a FormData object and append the form data to it
//     const formData = new FormData(form);

//     const options = {
//         xsrfCookieName: 'XSRF-TOKEN',
//         xsrfHeaderName: 'X-XSRF-TOKEN',
//     }
//     // send form data using axios post method

//     axios.post('/member/profilePage/profileImg', formData, options)
//     .then((response) => {

//       id('profilePicsNotification').innerHTML = response.data
//       log(response.data, "profilePicsNotification")

//       if(response.data.message === "Profile image updated") {
//         id('profilePicsNotification').classList.add('w3-green')
//         id('profilePicsNotification').innerHTML = response.data.message
//         // Reload the page
//         location.reload();

//       }
//     })
//     .catch((error) => {
//        id('profilePicsNotification').classList.add('w3-red')
//       id('profilePicsNotification').innerHTML = error.message
//     });
  

//   id('profilePicsNotification').innerHTML = ""
// })