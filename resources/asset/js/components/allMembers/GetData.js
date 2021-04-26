import axios from "axios";
import { id } from "./../../global"


const getData = async () => {

    // get the search value;
    const searchInput = id('searchFamily').value;

    const api = await axios.create({ baseURL: 'http://localhost:80' });
    api.get('/allMembers')
        .then(response => {

            response.data.forEach((el) => console.log(el))

            response.data.filter(data => {
                return data.firstName.toLowerCase().includes(this.state.input.toLowerCase())
            })


        })
        .catch(err => console.error(err))

}