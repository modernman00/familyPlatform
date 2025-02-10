import { log, showError, checkManyElements, id, msgException } from '../global'
import { appendNewPost, allPost } from './post'
import { render } from "timeago.js"
import { appendNewComment } from './comment'

import axios from "axios"


// set an empty array
try {
    const appendedComments = new Set(); // To track unique comments
    const appendedPosts = new Set(); // To track unique comments

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

    const updatePost = async (e) => {
        log("wear")
        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);
        // Only append if the comment hasn't been added before
        if (!appendedPosts.has(dataForUse.post_no)) {
            appendedPosts.add(dataForUse.post_no);

            appendNewPost(dataForUse)

            try {
                await axios.put(`/updatePostByStatusAsPublished/${dataForUse.post_no}`, { post_status: 'published' });

            } catch (error) { console.error(`Failed to update comment status: ${error.message}`); }

        }
    };

    const updateComment = async (e) => {
        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);
        //   log(dataForUse)
        // Only append if the comment hasn't been added before
        if (!appendedComments.has(dataForUse.comment_no)) {
            appendedComments.add(dataForUse.comment_no);

            // check if dataForUse length is greater than 0 and if yes foreach to lop 

                    appendNewComment(dataForUse)

                try {
                    await axios.put(`/updateCommentByStatusAsPublished/${dataForUse.comment_no}`, { comment_status: 'published' });

                } catch (error) { console.error(`Failed to update comment status: ${error.message}`); }
         
        }
    };

    const updateLike = (e) => {
        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);
        const likeElement = id(dataForUse.likeHtmlId);
        if (likeElement) {
            likeElement.innerHTML = parseInt(dataForUse.likeCounter)
        }
    };

    

  

    // Establish an EventSource for receiving like updates
    // const connectSSE = (url, event, callbackFn) => {
    //     const sse = new EventSource(url);

    //     sse.addEventListener(event, callbackFn);
    //     // Reconnect on error or if the connection is closed
    //     sse.onerror = (err) => {
    //         console.warn("SSE connection lost, reconnecting...", err);
    //         sse.close();
    //         // startLongPolling();
    //         // Correctly pass the arguments on reconnect
    //         setTimeout(() => connectSSE(url, event, callbackFn), 5000); // Attempt to reconnect after 5 seconds
    //     };
    // };


    // connectSSE("/post/getNewPost", "updatePost", updatePost);
    // connectSSE("/comment/newComment", "updateComment", updateComment);
    // connectSSE("/profileCard/getLikes", "updateLike", updateLike);

    // POLLING FUNCTION 

    // Function to perform long polling for a given endpoint
    const fetchPollingData = async (endpoint, updateFunction) => {
        try {
            const response = await axios.get(endpoint, { timeout: 30000 });

            if (Array.isArray(response.data.message)) {
                log(response.data.message)
                response.data.message.forEach(item => updateFunction(item))

            }
        } catch (error) {
            if (error.code === 'ECONNABORTED') {
                console.warn(`Long polling connection timed out for ${endpoint}. Retrying...`);
            } else {
                console.error(`Error fetching data from ${endpoint}:`, error);
            }
        }
    };

    // Main long polling function
    (async function startLongPolling() {
        while (true) {

            await Promise.all([
                fetchPollingData('/getNewPostPolling', updatePost),
                fetchPollingData('/getNewCommentPolling', updateComment),
                fetchPollingData('/getNewLikesPolling', updateLike)
            ]);

            await new Promise(resolve => setTimeout(resolve, 2000)); // Wait 5 seconds before retrying
        }
    })();

    // AUTOMATICALLY UPDATE TIMESTAMP
    // Function to check for elements and render if they exist every 5 seconds
    setInterval(() => {
        checkManyElements('class', ".timeago", render);
        checkManyElements('class', ".commentTiming", render);
    }, 2000); // Adjust interval as needed


    const checkOriginAndParsedData = (data) => {
        if (!data) throw new Error('No update received');
        if (data) {
            if (data.origin != appUrl) { msgException('Invalid Origin'); }
            return data
        }
        
        // check if data is a valid jason object
        // return JSON.parse(data)
    }

} catch (error) {
    showError(error)
}