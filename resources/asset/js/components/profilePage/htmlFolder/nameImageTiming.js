import { format } from "timeago.js"
import { toSentenceCase} from "@shared"
const timeAgo = (x) => format(x)

// const fullName = (fullName) => {
//   return `<h6 id="fullName"><b>${fullName}</b> </h6>`
// }

// const postedAt = (date) => {
//   return `<div class="timeago postTimeCal w3-right w3-opacity"  datetime='${date.date_created}' title='${format(date.date_created)}'> ${timeAgo(date.post_time)}</div>`
// }


// export const nameImgTiming2 = (data) => {


//     const img = (data.profileImg) ? `/public/img/profile/${data.profileImg}` : "/public/avatar/avatarF.png"

//     return `<a href="/profilepage/img?dir=img&pics=${data.img}&pID=${data.post_no}&path=profile&id=${data.id}"> 
//       <img src=${img} alt="img" class="w3-left w3-circle w3-margin-right postImg" style="width:60px">
//         </a>
//         ${postedAt(data)} ${fullName(data.fullName)}`
// }

export const nameImgTiming = (data) => {
    const { profileImg, fullName, date_created, post_time} = data;

    const img = (profileImg) ? `/public/img/profile/${profileImg}` : "/public/avatar/avatarF.png"

    return `<div class="d-flex align-items-center mb-3">

            <img src="${img}" alt="Profile" class="rounded-circle me-3 postImg" width="40" height="40">
                        
            <div>
                <h6 class="mb-0">${toSentenceCase(fullName)} </h6>

                <small class="text-muted">posted </small><small class="text-muted timeago postTimeCal" title='${format(date_created)}' datetime='${date_created}'> ${timeAgo(post_time)}</small>
            </div>
            </div>`
}

