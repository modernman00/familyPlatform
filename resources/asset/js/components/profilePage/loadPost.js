import { removeDiv } from '../helper/general'
import { id, log } from '../global'
import { allPost, appendNewPost } from '../profilePage/html'
import { getApiData } from "../helper/http"
import { render } from "timeago.js"


// set an empty array
const state = { data: [] }

// 2. get the data from the database to set the inital data
const postData = getApiData("/post/getAllPost");

postData.then(response => {

    state.data = response.message;

    const serverConnection = new EventSource("/post/getAllPost/update");

    const updatePost = (e) => {
        if (e.origin != "http://olaogun.dev.com") {
            throw new Error("What is your origin?")
        }


        if (e.data) {
            const newPostData = JSON.parse(e.data)

            // check if the post no already exist
            let newData = state.data.some(el => el.post_no === newPostData.post_no);

            // if it is not available, add to the data state
            if (!newData) {
                state.data.push(newPostData)
            }
        }
        
        state.data.map(ele => appendNewPost(ele))

    }

    //    serverConnection.onopen = function(e) {
    //     console.log("Connection is opened");
    //   };



    serverConnection.addEventListener("update", (e) => updatePost(e))

    // addEventListener version
//  serverConnection.addEventListener('error', (e) => {
//   console.log("An error occurred while attempting to connect.");
// });
}).then()
.catch(err => log(err))



