import axios from "axios";


const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
}
export const getAllData = async () => {
   
  const response = await axios.get('http://olaogun.dev.com/allMembers3', config)
    .catch(err => err.message)
    return response.data
}