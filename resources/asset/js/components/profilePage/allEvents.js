"use strict";
import { id, log, msgException } from "../global"
import axios from "axios"


try {

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }
    const yourId = localStorage.getItem('requesterId');
    const famCode = localStorage.getItem('requesterFamCode');

    // CLICK EVENT get the comment and like button from the document
    document.onclick = async (e) => {

        const elementId = e.target.id
        const postId = e.target.name

        if (elementId.includes("likeButton")) {


            // replace button with Counter to get the span id 
            const likeCounterId = elementId.replace('Button', 'Counter')
            let likeCounterVal = id(likeCounterId).innerHTML.trim(); // trim removes leading and trailing spaces
            likeCounterVal = likeCounterVal.replace(/\n/g, '')
            const encodedLikeCounterVal = encodeURIComponent(likeCounterVal);

            const result = await axios.put(`/profileCard/postLikes?postId=${postId}&count=${encodedLikeCounterVal}&likeCounterId=${likeCounterId}`)

            // Make the comment form to appear onclick. initcomment is the id of the comment button 
        } else if (elementId.includes("initComment")) {

            const commentFormId = elementId.replace('init', 'form')

            id(commentFormId).style.display = "block"

            // Submit function for comment using POST API
        } else if (elementId.includes("submitComment")) {      //elementId == submitComment511

            // 0.5 LISTEN FOR THE SUBMIT EVENT
            // 0.7 GET THE COMMENT FORM ID 
            // 1. POST SENDS BACK THE LAST COMMENT NO POSTED
            // 2. SEND IT TO THE EVENT SOURCE OBJECT AT LOADPOST.JS

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

                // 1.
                const response = await axios.post('/postCommentProfile', formEntries, options)
                const result = response.data.message

            }
            // SUBMIT THE POST
        } else if (elementId.includes("submitPost")) {

            // LISTEN TO THE SUBMIT EVENT 
            // 2. GET THE FORM id
            // 3. POST TO THE SERVER USING AXIOS POST
            //4. GET THE POST FROM THE SERVER USING AXIOS GET 
            //5. SEND IT TO THE EVENT SOURCE OBJECT AT LOADPOST.JS 

            e.preventDefault()
            const formExtra = id('formPostMessageModal')
            const formData = new FormData(formExtra)
            // get the requesterFamCode from the localStorage 
            const requesterFamCodeValue = localStorage.getItem('requesterFamCode');
            // Append the new form entry to the FormData object
            formData.append('postFamCode', requesterFamCodeValue);

            // 3. 
            const response = await axios.post("/member/profilePage/post", formData, options)

            const result = response.data.message

            const getNewResponse = await axios.get("/post/getNewPostAndEmail?newCommentNo=" + result);

            log(getNewResponse.data.message);

            id('id01').style.display = 'none'


            id("formPostMessageModal").reset();

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


    }
} catch (e) { 
    showError(e)
}




