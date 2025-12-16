import { log, showError, checkManyElements, id, msgException, qSel } from '../global'
import { appendNewPost, allPost } from './post'
import { render } from "timeago.js"
import { appendNewComment } from './comment'
import Pusher from "pusher-js"
import axios from "axios"
import { eventHtml } from './eventHTML'
import { addToNotificationTab, increaseNotificationCount } from '../navbar'
import {friendRequestCard} from './htmlFolder/friendRequestCard'

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
        pagination: {},

        // Method to fetch initial data and populate state
        async initialize(page = 1) {
            try {
                const pullData = await axios.get(`/post/getAllPostCommentByFamCode?page=${page}&limit=50`);

                // --- THE FIX IS HERE ---
                // Manually parse the data string before using it
                let responseData = pullData.data;

                // Check if the data is a string and needs parsing (based on your screenshot)
                if (typeof responseData === 'string') {
                    responseData = JSON.parse(responseData);
                }

                // 1. Assign the new 'posts' array from the parsed response
                // Handle new response structure with pagination
                if (responseData.message && responseData.message.message) {
                    this.posts = responseData.message.message;
                    this.pagination = responseData.message.pagination;
                } else {
                    // Fallback for old structure or direct array
                    this.posts = responseData.message || [];
                }

                // Clear existing posts if it's a new page load (optional, depends on UX)
                // For now, we append, but for pagination usually we clear or replace.
                // Let's clear the container for standard pagination behavior
                id('postIt').innerHTML = '';

                if (this.posts.length > 0) {
                    // 2. Loop and render.
                    this.posts.forEach(post => allPost(post));
                    this.renderPagination();
                } else {
                    log("No post");
                    id('postIt').innerHTML = '<p class="text-center text-muted mt-4">No posts found.</p>';
                }

            } catch (error) {
                console.error("Error fetching posts and comments:", error);
                showError(error);


            }
        },

        renderPagination() {
            const paginationEl = id('feedPagination');
            if (!paginationEl || !this.pagination) return;

            const { current_page, last_page } = this.pagination;
            let html = '';

            // Previous Button
            html += `
                <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${current_page - 1}" tabindex="-1">Previous</a>
                </li>
            `;

            // Page Numbers (Simplified range for now)
            for (let i = 1; i <= last_page; i++) {
                // Show first, last, current, and neighbors
                if (i === 1 || i === last_page || (i >= current_page - 1 && i <= current_page + 1)) {
                    html += `
                        <li class="page-item ${i === current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                } else if (i === current_page - 2 || i === current_page + 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Next Button
            html += `
                <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${current_page + 1}">Next</a>
                </li>
            `;

            paginationEl.innerHTML = html;

            // Add event listeners
            paginationEl.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const newPage = parseInt(e.target.dataset.page);
                    if (newPage && newPage !== current_page && newPage > 0 && newPage <= last_page) {
                        this.initialize(newPage);
                        // Scroll to top of feed
                        document.querySelector('.feed-column').scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        }
    };

    // initiate the global object
    state.initialize();

    const addNewPost = async (e) => {
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

    const addNewComment = async (e) => {
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

    const famCode = localStorage.getItem('requesterFamCode');
    const addNewFriendRequest = (data) => {
         // ADD TO THE NOTIFICATION TAB OF THE APPROVER if the famcode on local storage is the same as the approverFamCode



      if (famCode === data.approverDetails.approverCode) {
        addToNotificationTab(data);
        friendRequestCard(data.requesterDetails);
        increaseNotificationCount();
      } 
    }

    // Subscribe to the posts channel
    const postsChannel = pusher.subscribe('posts-channel');
    postsChannel.bind('new-post', (data) => {
        data.forEach(item => addNewPost(item))
    });

    // Subscribe to the comments channel
    const commentsChannel = pusher.subscribe('comments-channel');
    commentsChannel.bind('new-comment', (data) => {
        data.forEach(item => addNewComment(item))
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

// Subscribe to the new friend request channel
    const friendRequestChannel = pusher.subscribe('friend-request-channel');
    friendRequestChannel.bind('new-request', (data) => {
       if (famCode === data.approverDetails.approverCode) {
       addNewFriendRequest(data)
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