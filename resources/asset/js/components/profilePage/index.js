"use strict";

import "./loadPost"

// const postDataI = () => {
//     getApiData(`/post/getAllPost`)
//             .then(data => {
//                 console.log(data.message)
//                 data.message.map(el => { 
//                     allPost(el)
//                 })
//             })
//             .catch(err => {console.log(err)})
// }

 

import './homePage'
import "./createPost"
import "./img"
import "./comment"
import "./event"

// import { allPost } from "../profilePage/html"

// var source = new EventSource("/post/getAllPost");
//     source.onmessage = function (event) {
//         console.log(event)
//         const data = JSON.parse(event.data)
//         console.log(data)
//         data.map(el => allPost(el))
//     }
