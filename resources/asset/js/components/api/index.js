import axios from "axios";


const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
}

const URL = process.env.MIX_APP_URL2
    // https: //laravel.com/docs/5.4/mix#environment-variables

export const getAllData = async() => {
    // const response = await axios.get(`${URL}/allMembers/processApiData`, config)
    const response = await axios.get(`${URL}allMembers/processApiData2`, config)
        .catch(err => err.message)
    return response.data
}

export const postData = async(url, object) => {
    await axios.post(url, object)
        .then((response) => {
            console.log(response);
        })
        .catch((error) => {
            console.log(error);
        });
}