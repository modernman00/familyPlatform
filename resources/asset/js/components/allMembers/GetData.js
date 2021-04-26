import axios from "axios";
import { id, log } from "./../../global"
import { realTimeServer} from "../../helper"

id('searchFamily').addEventListener('keyup', (event) => {
        const value = event.target.value
        log(value)
    })

 
    // const config = {
    //     headers: {
    //         'Content-Type': 'application/json'
    //     },
    //     data: {},
    //     }

    // axios.get('http://olaogun.dev.com/allMembers',config)
    //     .then((response) => {
    //         log(response.data);
    //         log(response.headers);
    //         })
    //     .catch(err => console.error(err))
