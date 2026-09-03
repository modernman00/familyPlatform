import { format } from "timeago.js"
import { esc } from "../../global"

const timeAgo = (x) => (x ? format(x) : '')

const fullName = (name) => {
  return `<h6 id="fullName"><b>${esc(name || 'Unknown User')}</b> </h6>`
}

const postedAt = (date) => {
  if (!date?.date_created || !date?.post_time) return '';
  return `<div class="timeago postTimeCal w3-right w3-opacity" datetime='${esc(date.date_created)}' title='${esc(format(date.date_created))}'> ${timeAgo(date.post_time)}</div>`
}

const familyBadge = (famCode) => {
  return famCode ? `<span class="w3-badge w3-small w3-blue w3-margin-left" style="font-weight: normal; padding: 2px 6px;">Family: ${esc(famCode)}</span>` : '';
}

export const nameImgTiming = (data) => {

    const img = esc((data?.profileImg) ? `/resources/images/profile/${data.profileImg}` : "/public/avatar/avatarF.png")
    const pId = esc(data?.post_no || '');
    const uId = esc(data?.id || '');
    const imgParam = encodeURIComponent(data?.img || '');

    return `<a href="/profilepage/img?dir=img&pics=${imgParam}&pID=${pId}&path=profile&id=${uId}"> <img src="${img}" alt="img" class="w3-left w3-circle w3-margin-right postImg" style="width:60px">
        </a>
        ${postedAt(data)}
        <div style="display: flex; align-items: center;">
            ${fullName(data?.fullName)} ${familyBadge(data?.postFamCode)}
        </div>`
}
