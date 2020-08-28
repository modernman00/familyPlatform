import { id, idValue, log } from "./../../../global";



// id('kids').addEventListener('onchange', (event) => {alert(event.target.value)
// log(event.target.value)
// }
// )

const show = (e) => {
    id('kids_help').innerHTML = e.target.value
}




document.getElementById('kids').addEventListener('change', show)

// id('kids').onchange = show