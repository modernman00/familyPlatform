import { log, showError, checkManyElements, id } from '../global'
import { appendNewPost, allPost } from './post'
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
                } else {
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


        // Check if data contains the expected properties
        if (!dataForUse || !dataForUse.likeCounter || !dataForUse.likeHtmlId) {
            throw new Error("Invalid data format received for like update:");

        }

        // // Update the like count in the targeted HTML element
        const likeElement = id(dataForUse.likeHtmlId);

        if (likeElement) {
            likeElement.innerHTML = parseInt(dataForUse.likeCounter)
        }
    };

    const appendedComments = new Set(); // To track unique comments
    const appendedPosts = new Set(); // To track unique comments

    // Establish an EventSource for receiving like updates
    // const connectSSE = (url, event, callbackFn) => {
    //     const sse = new EventSource(url);

    //     sse.addEventListener(event, callbackFn);
    //     // Reconnect on error or if the connection is closed
    //     sse.onerror = (err) => {
    //         console.warn("SSE connection lost, reconnecting...", err);
    //         sse.close();
    //         // Correctly pass the arguments on reconnect
    //         setTimeout(() => connectSSE(url, event, callbackFn), 5000); // Attempt to reconnect after 5 seconds
    //     };
    // };


    // connectSSE("/post/getNewPost", "updatePost", updatePost);
    // connectSSE("/comment/newComment", "updateComment", updateComment);
    // connectSSE("/profileCard/getLikes", "updateLike", updateLike);



    // const sseComment = new EventSource("/comment/newComment");
    // sseComment.addEventListener('updateComment', updateComment);

    // const ssePost = new EventSource("/post/getNewPost");
    // ssePost.addEventListener('updatePost', updatePost);

    // const sseLikes = new EventSource("/profileCard/getLikes");
    // // Event listener for likes updates
    // sseLikes.addEventListener('updateLike', updateLike);


    // sseComment.addEventListener("error", (e) => {
    //     if (e.target.readyState === EventSource.CLOSED) {
    //         console.error("Connection was closed. Retrying...");
    //     }
    // })

    // POLLING FUNCTION 

   const startPolling = () => {
    setInterval(async () => {
        try {
            const getPostPolling = await axios.get('/getNewPostPolling');
            if (getPostPolling.data.message) {
                console.log(getPostPolling.data.message);
                updatePost(getPostPolling.data.message);
            }

            const getCommentPolling = await axios.get('/getNewCommentPolling');
            if (getCommentPolling.data.message) {
                console.log(getCommentPolling.data.message);
                updatePost(getCommentPolling.data.message);
            }
        } catch (error) {
            console.error('Error during polling:', error);
        }
    }, 10000);
}

startPolling();



    // AUTOMATICALLY UPDATE TIMESTAMP
    // Function to check for elements and render if they exist every 5 seconds
    setInterval(() => {
        checkManyElements('class', ".timeago", render);
        checkManyElements('class', ".commentTiming", render);
    }, 5000); // Adjust interval as needed


    const checkOriginAndParsedData = (data) => {
        if (!data) throw new Error('No update received');

        if (data.origin != appUrl) {
            console.warn("Invalid origin detected:");
            throw new Error('No update received');

        }

        return JSON.parse(data.data)
    }





} catch (error) {
    showError(error)
}