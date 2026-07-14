import { format } from "timeago.js"
const timeAgo = (x) => (x ? format(x) : '')

const fullName = (fullName) => {
  return `<h6 id="fullName"><b>${fullName || 'Unknown User'}</b> </h6>`
}

const postedAt = (date) => {
  if (!date?.date_created || !date?.post_time) return '';
  return `<div class="timeago postTimeCal w3-right w3-opacity"  datetime='${date.date_created}' title='${format(date.date_created)}'> ${timeAgo(date.post_time)}</div>`
}

const familyBadge = (famCode) => {
  return famCode ? `<span class="w3-badge w3-small w3-blue w3-margin-left" style="font-weight: normal; padding: 2px 6px;">Family: ${famCode}</span>` : '';
}

export const nameImgTiming = (data) => {

    const img = (data?.profileImg) ? `/public/img/profile/${data.profileImg}` : "/public/avatar/avatarF.png"
    const pId = data?.post_no || '';
    const uId = data?.id || '';
    const imgParam = data?.img || '';

    return `<a href="/profilepage/img?dir=img&pics=${imgParam}&pID=${pId}&path=profile&id=${uId}"> <img src=${img} alt="img" class="w3-left w3-circle w3-margin-right postImg" style="width:60px">
        </a>
        ${postedAt(data)} 
        <div style="display: flex; align-items: center;">
            ${fullName(data?.fullName)} ${familyBadge(data?.postFamCode)}
        </div>`
}