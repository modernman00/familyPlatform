"use strict";
import { id, log } from "../global"
import { showImageFileUploadFn } from "../helper/images"
import axios from "axios"

const profilePics = id('profilePics');
if (profilePics) {
  profilePics.addEventListener('click', () => {
    const formProfile = id('formProfilePics');
    if (formProfile) formProfile.style.display = "block";
  });
}

// FOR PROFILE IMAGE CHANGE
const uploadButtonProfilePics = id('uploadButtonProfilePics');
const profileImageFile = id('profileImageFile');
const profileImgFileNames = id('profileImgFileNames');
if (uploadButtonProfilePics && profileImageFile && profileImgFileNames) {
  showImageFileUploadFn('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames');
}

// FOR POST MODAL IMAGE UPLOAD  
const uploadButton = id('uploadButton');
const post_img = id('post_img');
const postModalImgFileNames = id('postModalImgFileNames');
if (uploadButton && post_img && postModalImgFileNames) {
  showImageFileUploadFn('uploadButton', 'post_img', 'postModalImgFileNames');
}

const submitProfilePics = id('submitProfilePics');
if (submitProfilePics) {
  submitProfilePics.addEventListener('click', () => {
    // Get the form element
    const form = document.getElementById("formProfilePics");
    if (!form) return;

    // Create a FormData object and append the form data to it
    const formData = new FormData(form);

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }
    // send form data using axios post method

    axios.post('/member/profilePage/profileImg', formData, options)
    .then((response) => {
      const profilePicsNotification = id('profilePicsNotification');
      if (profilePicsNotification) {
        profilePicsNotification.innerHTML = response.data;
        log(response.data, "profilePicsNotification");

        if (response.data.message === "Profile image updated") {
          profilePicsNotification.classList.add('w3-green');
          profilePicsNotification.innerHTML = response.data.message;
          // Reload the page
          location.reload();
        }
      }
    })
    .catch((error) => {
      const profilePicsNotification = id('profilePicsNotification');
      if (profilePicsNotification) {
        profilePicsNotification.classList.add('w3-red');
        profilePicsNotification.innerHTML = error.message;
      }
    });

    const profilePicsNotification = id('profilePicsNotification');
    if (profilePicsNotification) {
      profilePicsNotification.innerHTML = "";
    }
  });
}