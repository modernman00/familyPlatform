"use strict";
import { id, log } from "../global"
import { postFormData, getApiData } from "../helper/http"
import { commentHTML, appendNewPost } from '../profilePage/html'
import axios from "axios"
import Pusher from 'pusher-js';

try {

    Pusher.logToConsole = true;

    const pusher = new Pusher('d1f1e43f3d8afb028a1f', {
        cluster: 'eu'
    });

    // getApiData()

    let newLikeCounterVal = 0;

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }

    // let serverConnection = new EventSource("/post/getAllPost/update") 


    // const Pusher = (pushData) => {

    // // open the server sent event

    //     const update = (e) => {

    //         const data = JSON.parse(e.data)
    //         // log(data)

    //         if( appendNewPost(pushData)) {
    //             serverConnection.close()
    //         }

    //     }

    //     serverConnection.addEventListener("updatePost", update)

    // }

    const showTheComment = (commentResponse) => {
        const idDiv = `showComment${commentResponse.post_no}`
        const commentHtml = commentHTML(commentResponse)
        return id(idDiv).insertAdjacentHTML('afterbegin', commentHtml)

    }


    // CLICK EVENT get the comment and like button from the document
    document.onclick = (e) => {

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


            // Make the comment form to appear onclick
        } else if (elementId.includes("initComment")) {

            const commentFormId = elementId.replace('init', 'form')

            id(commentFormId).style.display = "block"

            // Submit function for comment using POST API
        } else if (elementId.includes("submitComment")) {      //elementId == submitComment511

            // 0.5 LISTEN FOR THE SUBMIT EVENT
            // 0.7 GET THE COMMENT FORM ID 
            // 1. POST SENDS BACK THE LAST COMMENT NO POSTED
            // 2.  USE IT TO GET THE NEW COMMENT
            // 3. ADD THE NEW COMMENT TO THE COMMENT DIV 
            // get the specific form id
            e.preventDefault()

            // 0.7
            const idForm = elementId.replace("submit", "form") //idForm == formComment511

            id(idForm).style.display = "none"   // make the comment form disappear

            // extract the form entries
            const form = id(idForm)
            let formEntries = new FormData(form)



            // 1.
            axios.post('/postCommentProfile', formEntries, options)
                .then(response => {
                    // 2. note. message returns the new post_no from the database
                    axios.get(`/member/pp/comment/byNumber?commentNo=${response.data.message}`)
                        .then(res => {
                            // 3.
                            showTheComment(res.data.message)
                        })
                }
                ).catch(error => {
                    log(error)
                })

            // SUBMIT THE POST
        } else if (elementId.includes("submitPost")) {

            // LISTEN TO THE SUBMIT EVENT 
            // 2. GET THE FORM id
            // 3. POST TO THE SERVER USING AXIOS POST
            //4. GET THE POST FROM THE SERVER USING AXIOS GET 
            //5. APPEND THE NEW POST TO THE POSTCARD 

            // 2. 
            const formExtra = id('formPostMessageModal')
            const formData = new FormData(formExtra)

            // 3. 
            axios.post("/member/profilePage/post", formData, options)
                .then(response => {

                  //  4. 
                    axios.get(`/post/getAllPost/byNumber?postNo=${response.data.message}`)
                        .then(res => {
                            // 5. 

                            // log(res.data.message)
                            appendNewPost(res.data.message)

                            // Pusher(res.data.message)

                        })
                    // Enable pusher logging - don't include this in production

                    const channel = pusher.subscribe('my-channel');

                    channel.bind('updatePost', function (data) {
                        log("checking1")
                        log(data.message);
                        log("checking")
                    });
                    id('id01').style.display = 'none'
                })

        }
    }
} catch (e) {
    showError(e)
}




