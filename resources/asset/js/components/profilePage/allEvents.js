"use strict";
import { id, showError, formReset, fileUploadSizeValidation } from "../global"
import axios from "axios"



try {

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }

    // CLICK EVENT get the comment and like button from the document
    document.addEventListener('click', async (e) => {  //document.onclick = async (e) => {

        const elementId = e.target.id
        const postId = e.target.name



        // Handle Like Button Click
        if (elementId.includes("likeButton")) {

            // replace button with Counter to get the span id 
            const likeCounterId = elementId.replace('Button', 'Counter')

            // trim removes leading and trailing spaces
            let likeCounterVal = id(likeCounterId).innerHTML.trim().replace(/\n/g, ''); // 

            const encodedLikeCounterVal = encodeURIComponent(likeCounterVal);

            await axios.put(`/profileCard/postLikes?postNo=${postId}&count=${encodedLikeCounterVal}&likeCounterId=${likeCounterId}`)

            // update all members of similar famcode on their UIs using Pusher

            await axios.get("/getNewLikesPusher");



            // Make the comment form to appear onclick. 
        } else if (elementId.includes("initComment")) {
            const commentFormId = elementId.replace('init', 'form')
            id(commentFormId).style.display = "block"

            // Handle Comment Submission
        } else if (elementId.includes("submitComment")) {

            e.preventDefault()

            //idForm == formComment511
            const idForm = elementId.replace("submit", "form")
            // make the comment form disappear
            id(idForm).style.display = "none"
            // extract the form entries
            const form = id(idForm)
            let formEntries = new FormData(form)

            // if the comment form input is empty. Get the input id and check 
            const inputComment = idForm.replace("form", "input")
            const idInputComment = id(inputComment);

            if (idInputComment.value == null || idInputComment.value == "") {
                alert("Please enter a comment before submitting")
            } else {

                await axios.post('/postCommentProfile', formEntries, options)

                // update all members of similar famcode on their UIs using Pusher

                await axios.get("/getNewCommentPusher");

                formReset(idForm);


            }
            // SUBMIT THE POST
        } else if (elementId.includes("submitPost")) {

            e.preventDefault()

            // check if the post message is empty
            const postMessage = id('postMessage').value.trim();

            if (postMessage === "") {
                alert("Post message cannot be empty");
                return;
            }

            // validate the file input if any
            fileUploadSizeValidation('post_img', 3);


            const formExtra = id('formPostMessageModal')
            const formData = new FormData(formExtra)
            
            // get the requesterFamCode from the localStorage 
            const requesterFamCodeValue = localStorage.getItem('requesterFamCode');
            // Append the new form entry to the FormData object
            formData.append('postFamCode', requesterFamCodeValue);

            try {
                // 1. Send the POST request to submit the form data
                const response = await axios.post("/member/profilePage/post", formData, options);

                // 2. Notify members of similar famcode about the post by email
                // 3. Update all members of similar famcode on their UIs using Pusher
                await Promise.all([
                    axios.get("/post/getNewPostAndEmail?newPostNo=" + response.data.token),
                    axios.get("/getNewPostPusher")
                ]);

                formReset("formPostMessageModal");
                
            } catch (error) {
                console.error("An error occurred:", error.response.data.message);
                // Optionally, display an error message to the user
                id('formPostMessageModal_notification').innerHTML = 'There was an error submitting your post. Please try again later.';
                id('formPostMessageModal_notification').classList.add('is-danger');
                id('formPostMessageModal_notification').style.display = 'block';
            }

        }     
        // take you to the request card for approval or denial
        else if (e.target.classList.contains('linkRequestCard')) {
            // ONCE THE NOTIFICATION BAR IS CLICKED, IT SHOULD TAKE YOU TO BE FRIEND REQUEST CARD

            const friendRequestSection = id(`${e.target.getAttribute('data-id')}_linkRequestCard`);
            if (friendRequestSection) {
                friendRequestSection.scrollIntoView({ behavior: "smooth" });
            }
        }


    })
} catch (e) {
    showError(e)
}




