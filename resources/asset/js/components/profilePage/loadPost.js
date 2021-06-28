
import { id, log } from '../global'
import { appendNewPost, allPost, showComment } from '../profilePage/html'
import { getApiData, getMultipleApiData } from "../helper/http"
import { render } from "timeago.js"
import axios from "axios"

// set an empty array
try {

    const state = {
        post: [],
        comment: []
    }

    // 2. get the data from the database to set the inital data
    const postData = getMultipleApiData("/post/getAllPost", '/member/pp/comment');

    postData.then(response => {

        state.post = response[0].data.message;

        state.comment = response[1].data.message

      state.post.map(data => allPost(data, state.comment))

        let serverConnection = new EventSource("/post/getAllPost/update")

        id("submitPost").addEventListener('click', () => {

            const updatePost = (e) => {
                if (e.origin != "http://olaogun.dev.com") {
                    throw new Error("What is your origin?")
                }

                if (e.data) {
                    const newPostData = JSON.parse(e.data)

                    let newData = state.post.some(el => el.post_no === newPostData.post_no); // check if the post no does not already exist

                    if (!newData) {   // if it is not available, add to the data state
                        state.post.push(newPostData)
                    }
                }

                return state.post.map(ele => appendNewPost(ele))

            }

            serverConnection.addEventListener("updatePost", (e) => updatePost(e))
        })

        // once the comment submit button is click 


        // serverConnection.addEventListener("updateComment", (e) => updatePost(e))


    }).catch(err => log(err))

} catch (error) {
    console.log(error)
}




