import { log, showError, checkManyElements } from '../global'
import { appendNewPost, allPost } from './post'
import { getMultipleApiData } from "../helper/http"
import { render } from "timeago.js"
import { appendNewComment } from './comment'

import axios from "axios"


// set an empty array
try {



    // Global state object with data-fetching and initialization logic
    const state = {
        post: [],
        comment: [],

        // Method to fetch initial data and populate state
        async initialize() {
            try {

                const pullData = await axios.get(`/post/getAllPostCommentByFamCode`);

                // Assign fetched data to state properties
                this.post = pullData.data.message.post;
                this.comment = pullData.data.message.comment;


                this.comment = this.comment.flat(); // Flatten the array of arrays into a single array of comment objects

                if (this.post.length > 0) {
                    // Render posts and comments on the page after data is loaded
                    this.post.forEach(data => allPost(data, this.comment));
                } else{
                    log("No post")
                }


            } catch (error) {
                console.error("Error fetching posts and comments:", error);
            }
        }
    };

    // initiate the global object
    state.initialize();


  const updatePost = (e) => {
        if (!e) throw new Error('No update received');

        // Validate origin to prevent unauthorized updates
        if (e.origin != appUrl) {
            console.warn("Invalid origin detected:", e.origin);
            return; // Exit if origin doesn't match
        }

        // Parse the incoming data and check if it already exists in state
        const dataForUse = JSON.parse(e.data);

  

        // Only append if the comment hasn't been added before
        if (!appendedPosts.has(dataForUse.post_no)) {
            appendedPosts.add(dataForUse.post_no);
            appendNewPost(dataForUse);
        }

    };

    const updateComment = (e) => {
        if (!e) throw new Error('No update received');

        // Validate origin to prevent unauthorized updates
        if (e.origin != appUrl) {
            console.warn("Invalid origin detected:", e.origin);
            return; // Exit if origin doesn't match
        }

        // Parse the incoming data and check if it already exists in state
        const dataForUse = JSON.parse(e.data);

        // Only append if the comment hasn't been added before
        if (!appendedComments.has(dataForUse.comment_no)) {
            appendedComments.add(dataForUse.comment_no);
            appendNewComment(dataForUse);
        }

    };

    const appendedComments = new Set(); // To track unique comments
    const appendedPosts = new Set(); // To track unique comments

    // DESTROY THE LOCALSTORAGE

    const serverConnection = new EventSource("/comment/newComment");

    const serverConnectionPost = new EventSource("/post/getNewPost");

    // Event listener for comment updates
    serverConnection.addEventListener('updateComment', updateComment);

     // Event listener for post updates
    serverConnectionPost.addEventListener('updatePost', updatePost);

    serverConnection.addEventListener("error", (e) => {
    if (e.target.readyState === EventSource.CLOSED) {
        console.error("Connection was closed. Retrying...");
    }
    })

    // AUTOMATICALLY UPDATE TIMESTAMP
    // Function to check for elements and render if they exist

    // AUTOMATICALLY UPDATE TIMESTAMP
    // Function to check for elements and render if they exist every 5 seconds
    setInterval(() => {
        checkManyElements('class', ".timeago", render);
        checkManyElements('class', ".commentTiming", render);
    }, 5000); // Adjust interval as needed



    // const updateTimingElements = () => {
    //     const updatePostTiming = document.querySelectorAll(".timeago");
    //     const updateCommentTiming = document.querySelectorAll(".commentTiming");

    //     // Check if elements exist before calling render function
    //     if (updatePostTiming.length > 0) {
    //         render(updatePostTiming);
    //     }
    //     if (updateCommentTiming.length > 0) {
    //         render(updateCommentTiming);
    //     }
    // };




} catch (error) {
    showError(error)
}