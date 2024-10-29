"use strict";
import { id, log } from "../global"
import { getApiData } from "../helper/http"
import { appendNewComment } from './comment'
import { appendNewPost } from './post'
import axios from "axios"
import filterMembersByFamCode from '../allMembers/filterMembersByFamCode';
// import Pusher from 'pusher-js';

try {

    // Enable pusher logging - don't include this in production

    // Pusher.logToConsole = true;

    // const pusher = new Pusher('d1f1e43f3d8afb028a1f', {
    //     cluster: 'eu'
    // });

    // getApiData()

    let newLikeCounterVal = 0;

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }


    // CLICK EVENT get the comment and like button from the document
    document.onclick = async (e) => {

        const elementId = e.target.id
        const postId = e.target.name


        if (elementId.includes("likeButton")) {
            // replace button with Counter to get the span id 
            const likeCounterId = elementId.replace('Button', 'Counter')

            const likeCounterVal = id(likeCounterId).innerHTML;

            // get the post like using the post id

            getApiData(`/profileCard/getLikes?postId=${postId}&count=${likeCounterVal}`);

            // add one to the result 
            newLikeCounterVal = parseInt(likeCounterVal) + 1;
            id(likeCounterId).innerHTML = newLikeCounterVal;


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

            // 2. 
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

        }

    }
} catch (e) {
    showError(e)
}




