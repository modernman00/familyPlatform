import { removeDiv } from '../helper/general'
// import { getApiData } from "../helper/http"
import { allPost, pictures} from "../profilePage/html"

    // open connection to the server
    var serverConnection = new EventSource("/post/getAllPost"); 

    // start listening for messages from the server by attaching a handler for the message event

    serverConnection.onmessage = function (event) {
   
        const data = JSON.parse(event.data)
        console.log(data)
         removeDiv('postIt')
     
        data.map(el => {
            allPost(el)
        
            })
    
    }

