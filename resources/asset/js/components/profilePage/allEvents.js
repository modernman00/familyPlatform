"use strict";
import { id, log, showError, formReset, fileUploadSizeValidation, initializeImageModal } from "../global"
import axios from "axios"
import { showEmojiPicker } from '../emojiPicker.js';
import { cacheReaction, getCachedReaction } from './indexDB/reactions.js';
import { renderTopReactions } from './showEmojiOnComment.js'



try {

    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }

    // CLICK EVENT get the comment and like button from the document
    document.addEventListener('click', async (e) => {  //document.onclick = async (e) => {

        const elementId = e.target.id
        const postId = e.target.name
        const postImgId = e.target.dataset.postimgid;

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
            id(commentFormId).classList.toggle('d-none');


        }
        // comment form emoji picker
        else if (elementId.includes("emojiToggle")) {

            const emojiToggler = id(elementId)

            const emojiListElement = elementId.replace('emojiToggle', 'emojiCommentPickerList')

            const emojiList = id(emojiListElement); // Container for emoji buttons
            // 🟡 Toggle emoji picker visibility when the toggle button is clicked
            // emojiToggler.addEventListener('click', () => {
            emojiList.classList.toggle('d-none'); // Show/hide the emoji list
            emojiToggler.setAttribute('aria-expanded', emojiList.classList.contains('d-none') ? 'false' : 'true');
            // });

            // comment form emoji picker
            showEmojiPicker(emojiListElement, 'data-commentEmoji-target');


        }

        // Handle Comment Submission
        else if (elementId.includes("submitComment")) {

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
                // redirect to the profile page
                window.location.href = '/profilePage';

            } catch (error) {
                console.error("An error occurred:", error.response);
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
        } // click event for the post images to show in a modal
        else if (e.target.classList.contains('grid-image')) {
            if (e.target.classList.contains('grid-image')) {
                const postNo = e.target.dataset.postno;
                const imgClass = `.zoomable-image${postNo}`;
                const imagesInGroup = Array.from(document.querySelectorAll(imgClass));
                const initialIndex = imagesInGroup.findIndex(img => img.src === e.target.src);

                if (initialIndex !== -1) {
                    initializeImageModal(imgClass, initialIndex, 'imageModal', 'modalImage', 'imageModalClose');
                }
            }

        }
        else if (elementId.includes('removeCommentIcon')) {
            // get the comment no
            const commentNo = elementId.replace('removeCommentIcon', '');
            // Ask user to confirm before deleting (safety check)
            if (confirm('Are you sure you want to remove this comment?')) {
                // Find the comment element and remove it from page
                const commentElement = id(`commentDiv${commentNo}`);
                if (commentElement) {

                    // remove from the database 
                    const response = await axios.delete(`/deleteComment/${commentNo}`);
                    alert(response.data.message)

                } else {
                    alert('Comment not found')
                }
            }

        }
        // redirect to the images pages
        // else if(elementId.includes('directToImages')){
        //     const user_id = localStorage.getItem('requesterId');
        //       window.location.href = `images/${user_id}`;
        // }

        // Find the closest element with a id starting with "like-button-"
        // else if(e.target.closest('[id^="like-button-"]')) {
        //     const likeButtonDiv = e.target.closest('[id^="like-button-"]');
        //     const likeButtonDivId = likeButtonDiv.getAttribute('id');
        //     const commentNo = likeButtonDiv.getAttribute('data-comment-no');
        //     const likeCount = id(`like-count-${commentNo}`).textContent.trim();

        //     // show the reaction bar 
        //     id(`reaction-bar-${commentNo}`).classList.add('show');

        //     // update the like count
        //     id(`like-count-${commentNo}`).textContent = parseInt(likeCount) + 1;

        // }
        else if (e.target.closest('[id^="reaction-option-"]')) {
            const reactionOptionDiv = e.target.closest('[id^="reaction-option-"]');
            const reactionOptionDivId = reactionOptionDiv.getAttribute('id');
            const commentNo = reactionOptionDiv.getAttribute('data-option-no');
            const emojiContent = reactionOptionDiv.textContent;
            const theLabel = reactionOptionDiv.dataset.label;

            // post to the comment_reactions table
            const response = await axios.post(`/commentReaction`, {
                comment_no: commentNo,
                reaction: emojiContent,
                label: theLabel,
            })

            const button = id(`like-button-${commentNo}`);
            const countEl = id(`like-count-${commentNo}`);
            const preview = id(`reaction-preview-${commentNo}`);

            // destructure the response to get the reaction summary
            const { label, reaction, countReaction } = response.data.message;
            if (label) button.querySelector('span').textContent = label;

            const emoji = renderTopReactions(countReaction, commentNo);
            if (reaction) preview.innerHTML = emoji;
            if (countEl) countEl.textContent = countReaction.totalReactions;

            // hide the reaction bar after selection
            const reactionBar = id(`reaction-bar-${commentNo}`);
            if (reactionBar) {
                reactionBar.classList.remove('show');
            }
        }

    });

    // MOUSE ENTER OVER THE LIKE BUTTON TO SHOW REACTION OPTIONS
    document.addEventListener('mouseover', async (e) => {

        const reactionDiv = e.target.closest('.reaction-button');

        //1 mouseover on the like button to show the reaction-option div
        if (reactionDiv) {

            const elementId = reactionDiv.id
            const elementName = reactionDiv.name
            const commentNo = reactionDiv.dataset.commentNo;

            //2 show the reaction bar - reaction-option class
            const reactionBar = id(`reaction-bar-${commentNo}`);
            if (reactionBar) {
                reactionBar.classList.toggle('show');
            } else {
                reactionBar.classList.remove('show');
            }
        }

        // MOUSE ENTER OVER THE REACTION PREVIEW OR COUNT TO SHOW TOOLTIP

        if (e.target.classList.contains('reaction-preview') || e.target.classList.contains('reaction-count')) {
            const commentNo = e.target.id.replace('reaction-preview-', '').replace('like-count-', '');
            // fetch and show the reaction summary tooltip  

            const getResponse = await axios.get(`/commentReactionSummary/${commentNo}`);
            const { counts, who } = getResponse.data.message;

            const whoList = who.map(u => `<div class="who-reacted">${u.reaction} ${u.firstName} ${u.lastName}</div>`).join('');

            // Create a summary of reactions that actually occurred
            const emojiSummary = Object.entries(counts)
                .filter(([reactionType, count]) => Number(count) > 0) // Keep only reactions with a count greater than 0
                .map(([reactionType, count]) => {
                    return `<span>${reactionType} ${count}</span>`; // Convert each to a span element
                })
                .join(' '); // Combine all spans into one string with spaces in between


            // tooltip display
            const tooltip = id(`reaction-summary-${commentNo}`);
            if (!tooltip) return;
            if (tooltip) tooltip.style.display = 'block';
            tooltip.innerHTML = `<div class="loading-tooltip">Loading...</div>`;
            tooltip.style.display = 'block';
            tooltip.innerHTML = `
                    <div class="tooltip-body">${whoList}</div>
            `;

            // Hide the tooltip after 5 seconds
            setTimeout(() => {
                tooltip.style.display = 'none';
            }, 4000);

            tooltip.classList.remove('show');

        }

    });

    document.addEventListener('mouseover', e => {
        const target = e.target.closest('.reaction-button');
        if (!target) return;
        const commentNo = target.dataset.commentNo;
        const tooltip = id(`reaction-summary-${commentNo}`);
        if (tooltip) tooltip.style.display = 'block';
    });



    document.addEventListener('mouseout', e => {
        const target = e.target.closest('.reaction-button');
        if (!target) return;
        const commentNo = target.dataset.commentNo;
        const tooltip = id(`reaction-summary-${commentNo}`);
        if (tooltip) tooltip.style.display = 'none';
    });




    // MOUSE LEAVE
} catch (e) {
    showError(e)
}




