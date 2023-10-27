import { log } from '../global'
import { appendNewPost, allPost} from './post'
import { getMultipleApiData } from "../helper/http"
import { render } from "timeago.js"


// set an empty array
try {

    const state = {
        post: [],
        comment: []
    }

    let serverConnection = new EventSource("/post/getAllPost/update") // open the server sent event

    // 2. get the data from the database to set the inital data
    const postData = getMultipleApiData("/post/getAllPost", '/member/pp/comment');

    postData.then(response => {

        state.post = response[0].data.message;

        state.comment = response[1].data.message

        state.post.map(data => allPost(data, state.comment)) // show all the post and comments

        // // let serverConnection = new EventSource("/post/getAllPost/update") // open the server sent event

        const updateComment = (e) => {

            const commentData = JSON.parse(e.data)

            let newData = state.comment.some(com => com.comment_no === commentData.comment_no); // check if the comment no does not already exist

            if (!newData) { // if it is not available, add to the data state
                state.comment.push(commentData)
            }
        }

        serverConnection.addEventListener("updateComment", (e) => updateComment(e))

        const updatePost = (e) => {
            if (e.origin != process.env.MIX_APP_URL2) {
                throw new Error("What is your origin?")
            }

            if (e.data) {
                const newPostData = JSON.parse(e.data)

                let newData = state.post.some(el => el.post_no === newPostData.post_no); // check if the post no does not already exist

                if (!newData) { // if it is not available, add to the data state
                    state.post.push(newPostData)
                }
            }
            return state.post.map(ele => appendNewPost(ele))

        }
        serverConnection.addEventListener("updatePost", (e) => updatePost(e))

        // // serverConnection.close()


        // AUTOMATICALLY UPDATE TIMESTAMP

        const updatePostTiming = document.querySelectorAll(".timeago")
        const updateCommentTiming = document.querySelectorAll(".commentTiming")

        render(updatePostTiming)
        render(updateCommentTiming)

    }).catch(err => log(err))

} catch (error) {
    //  console.log(error)
}