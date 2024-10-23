const reqId = localStorage.getItem('requesterId');
const famCode = localStorage.getItem('requesterFamCode');

console.log(famCode)


const filterMembersByFamCode = (data) => {
   // Check if data is an array before calling filter
  if (!Array.isArray(data)) {
    console.error('Error: data is not an array:');
  } 

    return data.filter(
      el => el.famCode === famCode || el.requesterCode === famCode || el.postFamCode === famCode
      );
};

export default filterMembersByFamCode;