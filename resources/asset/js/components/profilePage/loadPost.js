import { log, showError, checkManyElements, id, msgException, qSel } from '../global'
import { appendNewPost, allPost } from './post'
import { render } from "timeago.js"
import { appendNewComment } from './comment'
import Pusher from "pusher-js"
import axios from "axios"
import { eventHtml } from './eventHTML'
import { addToNotificationTab, increaseNotificationCount } from '../navbar'

// set an empty array
try {
    const MAX_APPENDED_POSTS = 1000; // Set a maximum limit
    const appendedComments = new Set(); // To track unique comments
    const appendedPosts = new Set(); // To track unique comments

    // Initialize Pusher
    const pusher = new Pusher(process.env.MIX_PUSHER_APP_KEY, {
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        encrypted: true,
    });

    // Global state object with data-fetching and initialization logic
    const state = {
        posts: [], // Renamed 'post' to 'posts' for clarity

        // Method to fetch initial data and populate state
        async initialize() {
            try {
              const pullData = await axios.get(`/post/getAllPostCommentByFamCode`);
            
            // --- THE FIX IS HERE ---
            // Manually parse the data string before using it
            let responseData = pullData.data;

            // Check if the data is a string and needs parsing (based on your screenshot)
            if (typeof responseData === 'string') {
                responseData = JSON.parse(responseData);
            }
                   
            // 1. Assign the new 'posts' array from the parsed response
            this.posts = responseData.message;

            if (this.posts.length > 0) {
                // 2. Loop and render.
                this.posts.forEach(post => allPost(post));
            } else {
                log("No post");
            }

            } catch (error) {
                console.error("Error fetching posts and comments:", error);
                showError(error);


            }
        }
    };

    // initiate the global object
    state.initialize();

    const updatePost = async (e) => {
        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);
        // Only append if the comment hasn't been added before
        if (!appendedPosts.has(dataForUse.post_no)) {
            appendedPosts.add(dataForUse.post_no);

            // Clean up old entries if the set exceeds the limit
            if (appendedPosts.size > MAX_APPENDED_POSTS) {
                const oldestPost = appendedPosts.values().next().value;
                appendedPosts.delete(oldestPost);
            }

            appendNewPost(dataForUse)

            try {
                await axios.put(`/updatePostByStatusAsPublished/${dataForUse.post_no}`, { post_status: 'published' });

            } catch (error) { console.error(`Failed to update comment status: ${error.message}`); }

        }
    };

    const updateComment = async (e) => {
        // Parse the incoming data and check if it already exists in state
        const dataForUse = checkOriginAndParsedData(e);

        // Only append if the comment hasn't been added before
        if (!appendedComments.has(dataForUse.comment_no)) {
            appendedComments.add(dataForUse.comment_no);

            // check if dataForUse length is greater than 0 and if yes foreach to lop 

            appendNewComment(dataForUse)

            const commentCounterEl = id(`commentCounter${dataForUse.post_no}`);
            if (commentCounterEl) {

                const commentCount = parseInt(commentCounterEl.textContent)
                // get the current value and convert it to a number 
                commentCounterEl.textContent = commentCount + 1;


            }


            try {
                await axios.put(`/updateCommentByStatusAsPublished/${dataForUse.comment_no}`, { comment_status: 'published' });

            } catch (error) { console.error(`Failed to update comment status: ${error.message}`); }

        }
    };

    const deleteComment = (data) => {
        const no = data.commentNo;
        const postNo = data.postNo;

        const commentEl = id(`commentDiv${no}`);
        if (commentEl) commentEl.remove();

        const commentCounterEl = id(`commentCounter${postNo}`);
        if (commentCounterEl) {

            const commentCount = parseInt(commentCounterEl.textContent)
            // get the current value and convert it to a number 

            if (commentCount > 0) {
                commentCounterEl.textContent = commentCount - 1;
            }

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

    const reactionUpdated = ({ commentNo, reaction, countReaction, whoReacted }) => {
        const likeCount = qSel(`#like-count-${commentNo}`);
        const preview = qSel(`#reaction-preview-${commentNo}`);

        if (likeCount) {
            likeCount.textContent = countReaction[reaction] || 0;
        }

        if (preview) {
            preview.innerHTML = `
            <span title="${whoReacted} reacted with ${reaction}">${reaction}</span>
        `;
        }

        const tooltip = id(`reaction-summary-${commentNo}`);
        if (tooltip) {
            tooltip.innerHTML = `<div><strong>${whoReacted}</strong> and others reacted</div>`;
        }
    }

    // Subscribe to the posts channel
    const postsChannel = pusher.subscribe('posts-channel');
    postsChannel.bind('new-post', (data) => {
        data.forEach(item => updatePost(item))
    });

    // Subscribe to the comments channel
    const commentsChannel = pusher.subscribe('comments-channel');
    commentsChannel.bind('new-comment', (data) => {
        data.forEach(item => updateComment(item))
    })
    commentsChannel.bind('delete-comment', (data) => {
        deleteComment(data)
    })
    commentsChannel.bind('reacted-updated', (data) => {
        if (!Array.isArray(data)) data = [data];
        data.forEach(item => reactionUpdated(item));
    })

    // Subscribe to the likes channel
    const likesChannel = pusher.subscribe('likes-channel');
    likesChannel.bind('like-event', (data) => {

        data.forEach(item => updateLike(item))
    });

    // Subscribe to the event channel


    const checkEventAndAdd = (data) => {

        const appendEvent = eventHtml(data);
        return id('eventList').insertAdjacentHTML('afterbegin', appendEvent);
    }

    const notificationChannel = pusher.subscribe('notification-channel');

    notificationChannel.bind('new-notification', (data) => {
        if (localStorage.getItem('requesterFamCode') === data.receiver_id) {
            checkEventAndAdd(data);
            addToNotificationTab(data);
            increaseNotificationCount();

        }
    });



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