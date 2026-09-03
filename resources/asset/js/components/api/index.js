import axios from "axios";
import { showError } from "@modernman00/shared-js-lib";


const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
}

const URL = '/';

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

/**
 * Ask the server whether a single email address already belongs to a registered
 * account. Replaces the old bulk fetch of every member email (SEC-4).
 * @param {string} email
 * @returns {Promise<boolean>}
 */
export const emailIsRegistered = async (email) => {
    try {
        const response = await axios.get(`${URL}getEmails`, {
            params: { email },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        return response?.data?.message?.exists === true;
    } catch (error) {
        // Fail closed-ish: treat as "not registered" so the invite path still works.
        console.error('emailIsRegistered check failed:', error);
        return false;
    }
};

