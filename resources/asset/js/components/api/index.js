import axios from "axios";
import { showError } from "../global";


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
    try {
        const response = await axios.get(`${URL}allMembers/processApiData2`, config);
        return response.data;
    } catch (error) {
        showError(error);
        // You can perform additional error handling actions if needed
        throw error;
    }
};

export const postData = async(url, object) => {
    try {
        const response = await axios.post(url, object);
        console.log(response);
    } catch (error) {
        showError(error);
        // You can perform additional error handling actions if needed
        throw error;
    }
};

export const fetchEmailData = ()=> {
    // Make a GET request and return the promise
    return axios.get(`${URL}getEmails`)
        .then(response => {
            // Extract the data from the response
            const data = response.data
            // Return the data or do further processing
            return data;
        })
        .catch(error => {
            // Handle any errors that occur during the request
            console.error('Error fetching data:', error);
            throw error; // Rethrow the error to be handled by the caller
        });
}

