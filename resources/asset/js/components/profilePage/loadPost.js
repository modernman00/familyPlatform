import { removeDiv } from '../helper/general'
import { id, log } from '../global'
import { allPost } from "../profilePage/html"
import axios from "axios"


// const config = {
//     headers: {
//       'X-Requested-With': 'XMLHttpRequest',
//       'Content-Type': 'application/json',
//       'Accept': 'application/json',
//     //   'Authorization': 'Bearer ' + token
//     },
//   }

// axios.get("/post/getAllPost", config)
// .then(response => response.data.message.map(el => {
//         allPost(el)
//         }))
// .catch(err => log(err.message))



// open connection to the server
var serverConnection = new EventSource("/post/getAllPost/update");

// start listening for messages from the server by attaching a handler for the message event

const newPostFn = (e) => {
    if(e.origin != "http://olaogun.dev.com") {
        throw new Error("What is your origin?")
    }

    
    const data = JSON.parse(e.data)
    log(e)

    // const count = localStorage.getItem('count')
    // if (count == data.length) {
        removeDiv('postIt')
        return data.map(el => {
            // log(el)
            allPost(el)
        })
    // }


    // localStorage.setItem('count', data.length)


}

serverConnection.onmessage = (e) => {
    newPostFn(e)
}

//serverConnection.addEventListener = ('newPost', newPostFn)

