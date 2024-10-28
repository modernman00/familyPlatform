import { log, showError } from '../global'
import { appendNewPost, allPost } from './post'
import { getMultipleApiData } from "../helper/http"
import { render } from "timeago.js"
import filterMembersByFamCode from '../allMembers/filterMembersByFamCode';
import axios from "axios"


// set an empty array
try {
    const state = {
        post: [],
        comment: []
    }

    let serverConnection = new EventSource("/post/getAllPost/update")

    // Use Promise.all to fetch allPost and allCOMMENT data in parallel
    const [thePost, allComment] = await Promise.all([
        axios.get(`/post/getAllPost`),
        axios.get(`/member/pp/comment`)
    ]);
    state.post = thePost.data.message;
    state.comment = allComment.data.message

    const filteredPost = filterMembersByFamCode(state.post)

    // show all the post and comments. the allPosts function filtered out the comments with the post_no, this happens once the page is loaded. 
    filteredPost.forEach(data => allPost(data, state.comment))

    // next - once a new post and comment, function to update the IU real time

    const updateData = (e, type) => {
        if (e.origin !== process.env.MIX_APP_URL2) {
            console.warn("Invalid origin detected:", e.origin);
            return; // Exit if origin doesn't match
        }

        const stateArray = (type === 'comment') ? state.comment : state.post
        const uniqueKey = type === "comment" ? "comment_no" : "post_no";

        const data = JSON.parse(e.data);
        // check if the post no does not already exist
        const isExisting = stateArray.some(item => item[uniqueKey] === data[uniqueKey]);

        if (!isExisting) { // if it is not available, add to the data state
            stateArray.push(data)
        }

       if (type === "post") return state.post.map(ele => appendNewPost(ele))

    }

    serverConnection.addEventListener("updateComment", (e) => updateData(e, "comment"))
    serverConnection.addEventListener("updatePost", (e) => updateData(e, "post"))

    // AUTOMATICALLY UPDATE TIMESTAMP
    const updatePostTiming = document.querySelectorAll(".timeago")
    const updateCommentTiming = document.querySelectorAll(".commentTiming")

    render(updatePostTiming)
    render(updateCommentTiming)


} catch (error) {
    showError(error)
}