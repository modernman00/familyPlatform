import { removeDiv, createAndAppendElement } from '../helper/general'
// import { getApiData } from "../helper/http"
import { allPost, pictures} from "../profilePage/html"

    var source = new EventSource("/post/getAllPost");
    source.onmessage = function (event) {
        // console.log(event)
        const data = JSON.parse(event.data)
        console.log(data)
         removeDiv('postIt')
         removeDiv('postItButton')
        data.map(el => {
            allPost(el)
            pictures(el)
            })
    
    }

