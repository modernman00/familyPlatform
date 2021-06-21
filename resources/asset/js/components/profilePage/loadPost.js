import { removeDiv } from '../helper/general'
import { id, log } from '../global'
import { allPost, appendNewPost } from '../profilePage/html'
import { getApiData } from "../helper/http"

// get the postdata which returns a promise
const postData = getApiData("/post/getAllPost");

// then get the result of the promise with the then function
postData.then(response => response.message.map(el => allPost(el))).catch(err => log(err))


const serverConnection = new EventSource("/post/getAllPost/update");

const newPostFn = (e) => {
    if(e.origin != "http://olaogun.dev.com") {
        throw new Error("What is your origin?")
    }
    const data = JSON.parse(e.data)

    log(e.lastEventId)

        return data.forEach(el => {
            log(el.post_no)
                appendNewPost(el)
            
        })
}

serverConnection.onmessage = (e) => newPostFn(e)













//serverConnection.addEventListener = ('newPost', newPostFn)

