import { log, showError, checkManyElements, id } from '../global'
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

                log(this.post)

                log(localStorage.getItem('requesterId'))

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
       

        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);

        // Only append if the comment hasn't been added before
        if (!appendedPosts.has(dataForUse.post_no)) {
            appendedPosts.add(dataForUse.post_no);
            appendNewPost(dataForUse);
        }

    };

    const updateComment = (e) => {
       
        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);

        // Only append if the comment hasn't been added before
        if (!appendedComments.has(dataForUse.comment_no)) {
            appendedComments.add(dataForUse.comment_no);
            appendNewComment(dataForUse);
        }

    };

     const updateLike = (e) => {
  
        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);

         const  newLikeCounterVal = parseInt(dataForUse.likeCounter);
            id(dataForUse.likeHtmlId).innerHTML = newLikeCounterVal;

    };

    const appendedComments = new Set(); // To track unique comments
    const appendedPosts = new Set(); // To track unique comments

    // DESTROY THE LOCALSTORAGE

    const sseComment = new EventSource("/comment/newComment");

    const ssePost = new EventSource("/post/getNewPost");

    const sseLikes = new EventSource("/profileCard/getLikes");

    // Event listener for comment updates
    sseComment.addEventListener('updateComment', updateComment);

     // Event listener for post updates
    ssePost.addEventListener('updatePost', updatePost);

      // Event listener for likes updates
    sseLikes.addEventListener('updateLike', updateLike);

    sseComment.addEventListener("error", (e) => {
    if (e.target.readyState === EventSource.CLOSED) {
        console.error("Connection was closed. Retrying...");
    }
    })


    // AUTOMATICALLY UPDATE TIMESTAMP
    // Function to check for elements and render if they exist every 5 seconds
    setInterval(() => {
        checkManyElements('class', ".timeago", render);
        checkManyElements('class', ".commentTiming", render);
    }, 5000); // Adjust interval as needed


    const checkOriginAndParsedData = (data)=> {
         if (!data) throw new Error('No update received');

          if (data.origin != appUrl) {
            console.warn("Invalid origin detected:");
            return; // Exit if origin doesn't match
        }

        return JSON.parse(data.data)
    }





} catch (error) {
    showError(error)
}