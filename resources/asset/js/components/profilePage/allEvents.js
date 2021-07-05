"use strict";
import { id, log } from "../global"
import { postFormData, getApiData } from "../helper/http"
import { commentHTML } from '../profilePage/html'
import axios from "axios"

try {

    // getApiData()

    let newLikeCounterVal = 0;

    // CLICK EVENT get the comment and like button from the document
    document.onclick = (e) => {

        const elementId = e.target.id
        const postId = e.target.name
        // const eClass = e.target

        // log(eClass)

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
        } else if (elementId.includes("submitComment")) {
            // get the specific form id
            e.preventDefault()
            //elementId == submitComment511

            const idForm = elementId.replace("submit", "form")
            //idForm == formComment511

            id(idForm).style.display = "none"   // make the comment form disappear

            // extract the form entries
            const form = id(idForm)

            let formEntries = new FormData(form)

            formEntries.delete('submit')
            formEntries.delete('checkbox_id')
            // formEntries.delete('token')

            const options = {
                xsrfCookieName: 'XSRF-TOKEN',
                xsrfHeaderName: 'X-XSRF-TOKEN',
            }

            // AXIOS POST FUNCTIONALITY
            axios.post('/postCommentProfile', formEntries, options)
                .then(response => {

                    // POST SENDS BACK THE LAST COMMENT NO POSTED
                    // USE IT TO GET THE NEW COMMENT
                    // ADD THE NEW COMMENT TO THE COMMENT DIV 

                    axios.get(`/member/pp/comment/byNumber?commentNo=${response.data.message}`)
                        .then(res => {
                            const postNo = res.data.message.post_no
                            const idDiv = `showComment${postNo}`
                            const commentHtml = commentHTML(res.data.message)
                            id(idDiv).insertAdjacentHTML('afterbegin', commentHtml)

                        })

                }
                ).catch(error => {
                    log(error)

                })





            // submit the post 
        } else if (elementId.includes("submitPost")) {


            postFormData("/member/profilePage/post", "formPostMessageModal")

            // make the post modal display disappear

            id('id01').style.display = 'none'

            // location.reload();   

        }
    }
} catch (e) {
    showError(e)
}




