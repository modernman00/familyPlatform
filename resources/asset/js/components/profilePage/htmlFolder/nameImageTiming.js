import { format } from "timeago.js"
const timeAgo = (x) => format(x)

const fullName = (fullName) => {
  return `<h6 id="fullName"><b>${fullName}</b> </h6>`
}

const postedAt = (date) => {
  return `<div class="timeago postTimeCal w3-right w3-opacity"  datetime='${date.date_created}' title='${format(date.date_created)}'> ${timeAgo(date.post_time)}</div>`
}


export const nameImgTiming = (data) => {


    const img = (data.profileImg) ? `/public/img/profile/${data.profileImg}` : "/public/avatar/avatarF.png"

    return `<a href="/profilepage/img?dir=img&pics=${data.img}&pID=${data.post_no}&path=profile&id=${data.id}"> <img src=${img} alt="img" class="w3-left w3-circle w3-margin-right postImg" style="width:60px">
        </a>
        ${postedAt(data)} ${fullName(data.fullName)}`
}