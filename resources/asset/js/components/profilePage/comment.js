"use strict";
import { get } from "lodash";
import { id, log } from "../global"
import { postFormData, getApiData } from "../helper/http"

try {

    getApiData()

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
        } else if (elementId.includes("submitComment")){
            // get the specific form id
            e.preventDefault()
            const idForm = elementId.replace("submit", "form")

            postFormData("/postCommentProfile", idForm, "/member/ProfilePage")

            location.reload();

            // getApiData()
        }   
    }
} catch (e) {
    showError(e)
}




