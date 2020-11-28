import { id } from "../../global";

try {
    const showModal = ()=> {
        return id('id01').style.display = 'block'
    }
    id('postMessage').addEventListener('click', showModal)
} catch (e) {
    console.log(e.message)
}


alert('helllllllo')




