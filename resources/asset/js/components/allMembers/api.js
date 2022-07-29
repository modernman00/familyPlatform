import axios from "axios";
import { id, log, } from "../global"
import { format, render } from "timeago.js"
// import { eventInput } from "./event"
import { loaderIcon } from "../helper/general"

const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
}

const renderHtml = (el) => {
    log(el)
    if (el) {
        id('allMembers').classList.remove('loader')

        const img = (el.img) ? `/img/profile/${el.img}` : "/avatar/avatarF.png"

        const html = `<div class="col-sm-3 mb-3" id=${el.id}>
        <div class="card">
         <img src="${img}" class="card-img-top allMember_profileImg" width="300" height="300" alt="profile img">
 
        <div class="card-body">
                    <h4 class='card-title'>${el.firstName} ${el.lastName}</h4>
                    <p class="card-text"><b>Alias:</b> ${el.alias} 
                    <br> <b>Father:</b>  ${el.fatherName}
                    <br> <b>Mother:</b> ${el.motherName}
                    <br> <b>Spouse:</b> ${el.spouseName && 'none'}
                    <br> <b>Contact:</b>  ${el.email} | ${el.mobile} 
                    <br> <b>Date joined:</b> ${format(el.date_created)}</p>
                    <a href="/allMembers/setProfile?id=${el.id}" class="btn btn-primary stretched-link">See Profile</a>
                </div>
                </div>
            </div>`

        id('allMembers').insertAdjacentHTML('beforeend', html)

    } else {

        return `<p> Sorry, we could find the data</p>`

    }

}

const URL = process.env.MIX_APP_URL2

axios.get(URL + '/allMembers/processApiData', config)
    .then(function(response) {

        loaderIcon()

        // add loader

        id('allMembers').classList.add('loader')

        id('allMembers').innerHTML = ""

        response.data.map(el => {

            renderHtml(el)
        })

        const input = () => {

            let inputVal = id('searchFamily').value

            if (inputVal == "") {

                id('allMembers').innerHTML = ""

                response.data.map(el => {

                    renderHtml(el)
                })
            }



            if (inputVal) {
                id('allMembers').innerHTML = ""

                let filter = response.data.filter(el => el.firstName.toLowerCase().includes(inputVal.toLowerCase()))
                log(filter)
                filter.map(el => {

                    renderHtml(el)
                })
            }


        }

        id('searchFamily').addEventListener('keyup', input)

        // log(searchInput)

        // const filteredData = data.filter(ele => {

        // })

        // response.data.map(el => {

        //     renderHtml(el)
        // })

    })
    // id('allMembers').classList.remove('loader')

.catch(err => console.error(err.message))