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
    if (el) {
        const theImg = `/img/profile/${el.img}`
        // const avatar = (el.gender === 'Male') ? `/img/profile/avatarM.png` : `/img/profile/avatarF.png`

        // console.log(avatar)

        localStorage.setItem("addFamilyButton", `${el.id}`)

        id('allMembers').classList.remove('loader')
        // const img = (el.img) 

        const html = `
        <div class="col-sm-3 mb-3" id=${el.id}>
            <div class="card">
                <img src="${theImg}" 
                    class="card-img-top allMember_profileImg" 
                    width="200" height="300" alt="profile img">
    
                <div class="card-body">
                            <h5 class='card-title'>${el.firstName} ${el.lastName}</h5>
                            <p class="card-text allMember_card_content">
                            <br> <b>Father:</b>  ${el.fatherName}
                            <br> <b>Mother:</b> ${el.motherName}
                            <br> <b>Spouse:</b> ${el.spouseName && 'none'}
                            <br> <b>Email:</b>  ${el.email} 
                    
                            <br> <b>Mobile:</b>   ${el.mobile} 
                            <br> <b>Id:</b>   ${el.id} 
                            <br> <b>Date joined:</b> ${format(el.date_created)}
                            </p>

                              <div class="card-body">
                            <a 
                            href="/allMembers/setProfile?id=${el.id}" 
                            class="btn btn-primary card-link" >
                               See Profile
                            </a> 
               
                            <button type="button" class="btn btn-success" id="addFamily${el.id}">
                                Add to family
                            </a>
                             </div>
                            
                </div>
                
             
            </div>
        </div>`

        id('allMembers').insertAdjacentHTML('beforeend', html)

    } else {

        return `<p> Sorry, we could find the data</p>`
    }

}

const URL = process.env.MIX_APP_URL2

axios.get(URL + 'allMembers/processApiData', config)
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