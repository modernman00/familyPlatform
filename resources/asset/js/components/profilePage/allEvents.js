"use strict";
import { id, log, msgException } from "../global"
import axios from "axios"
import { appendNewPost } from "./post";


try {

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }
    const yourId = localStorage.getItem('requesterId');
    const famCode = localStorage.getItem('requesterFamCode');

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



            }
            // SUBMIT THE POST
        } else if (elementId.includes("submitPost")) {

            e.preventDefault()
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
                    axios.get("/post/getNewPostAndEmail?newCommentNo=" + response.data.message),
                    axios.get("/getNewPostPusher")
                ]);

                // Hide the modal and reset the form
                id('id01').style.display = 'none';
                id("formPostMessageModal").reset();
            } catch (error) {
                console.error("An error occurred:", error);
                // Optionally, display an error message to the user
                alert("There was an error processing your request. Please try again.");
            }

        }     // add/delete to/from the notificatn bar 
        else if (elementId && elementId.includes('deleteNotification')) {
            // Extract the user ID from the target ID
            const senderId = elementId.replace("deleteNotification", "notificationBar");

            const elementData = id(elementId)
            const data = elementData.getAttribute("data-id");

            // change the background of the clicked element 


            const notificationHTML = id(senderId);

            // Make sure required variables are defined before using them
            if (
                typeof yourId === 'undefined' ||
                typeof famCode === 'undefined'
            ) {
                msgException("Required parameters (yourId or famCode) are not defined");
            }

            const url = `/removeNotification/${yourId}/${famCode}/${data}`


            const response = await axios.put(url)

            if (response.data.message === "success") {

                // remove a html element with notificationBar after 2 mins 
                notificationHTML.remove()

                // reduce the notification count as you have deleted the notification

                const newValues = parseInt(sessionStorage.getItem('notificationCount') - 1)
                id('notification_count').innerHTML = newValues;
            } else {
                msgException("Error removing notification");
            }
        } // take you to the request card for approval or denial
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




