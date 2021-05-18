import axios from "axios";


const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
}

export const getAllData = async () => {
    const response = await axios.get('http://olaogun.dev.com/allMembers/processApiData', config)
        .catch(err => err.message)
    return response.data
}

export const postData = async (url, object) => {
    await axios.post(url, object)
        .then((response)=> {
            console.log(response);
        })
        .catch((error)=> {
            console.log(error);
        });
}