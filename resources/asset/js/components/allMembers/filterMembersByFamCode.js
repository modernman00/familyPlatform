const reqId = localStorage.getItem('requesterId');


export const filterMembersByFamCode = (data, famCode) => {
  

    return data.filter(
      el => el.id !== reqId && (el.famCode === famCode || el.requesterCode === famCode)
      );
};